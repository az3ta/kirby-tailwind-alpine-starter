<?php

namespace Kirby\Api;

use Closure;
use Exception;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\F;
use Kirby\Http\Response;
use Kirby\Http\Router;
use Kirby\Toolkit\Pagination;
use Kirby\Toolkit\Properties;
use Kirby\Toolkit\Str;
use Throwable;

/**
 * The API class is a generic container
 * for API routes, models and collections and is used
 * to run our REST API. You can find our API setup
 * in `kirby/config/api.php`.
 *
 * @package   Kirby Api
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Api
{
    use Properties;

    /**
     * Authentication callback
     *
     * @var \Closure
     */
    protected $authentication;

    /**
     * Debugging flag
     *
     * @var bool
     */
    protected $debug = false;

    /**
     * Collection definition
     *
     * @var array
     */
    protected $collections = [];

    /**
     * Injected data/dependencies
     *
     * @var array
     */
    protected $data = [];

    /**
     * Model definitions
     *
     * @var array
     */
    protected $models = [];

    /**
     * The current route
     *
     * @var \Kirby\Http\Route
     */
    protected $route;

    /**
     * The Router instance
     *
     * @var \Kirby\Http\Router
     */
    protected $router;

    /**
     * Route definition
     *
     * @var array
     */
    protected $routes = [];

    /**
     * Request data
     * [query, body, files]
     *
     * @var array
     */
    protected $requestData = [];

    /**
     * The applied request method
     * (GET, POST, PATCH, etc.)
     *
     * @var string
     */
    protected $requestMethod;

    /**
     * Magic accessor for any given data
     *
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws \Kirby\Exception\NotFoundException
     */
    public function __call(string $method, array $args = [])
    {
        return $this->data($method, ...$args);
    }

    /**
     * Creates a new API instance
     *
     * @param array $props
     */
    public function __construct(array $props)
    {
        $this->setProperties($props);
    }

    /**
     * Runs the authentication method
     * if set
     *
     * @return mixed
     */
    public function authenticate()
    {
        if ($auth = $this->authentication()) {
            return $auth->call($this);
        }

        return true;
    }

    /**
     * Returns the authentication callback
     *
     * @return \Closure|null
     */
    public function authentication()
    {
        return $this->authentication;
    }

    /**
     * Execute an API call for the given path,
     * request method and optional request data
     *
     * @param string|null $path
     * @param string $method
     * @param array $requestData
     * @return mixed
     * @throws \Kirby\Exception\NotFoundException
     * @throws \Exception
     */
    public function call(string $path = null, string $method = 'GET', array $requestData = [])
    {
        $path = rtrim($path ?? '', '/');

        $this->setRequestMethod($method);
        $this->setRequestData($requestData);

        $this->router = new Router($this->routes());
        $this->route  = $this->router->find($path, $method);
        $auth   = $this->route->attributes()['auth'] ?? true;

        if ($auth !== false) {
            $user = $this->authenticate();

            // set PHP locales based on *user* language
            // so that e.g. strftime() gets formatted correctly
            if (is_a($user, 'Kirby\Cms\User') === true) {
                $language = $user->language();

                // get the locale from the translation
                $translation = $user->kirby()->translation($language);
                $locale = ($translation !== null) ? $translation->locale() : $language;

                // provide some variants as fallbacks to be
                // compatible with as many systems as possible
                $locales = [
                    $locale . '.UTF-8',
                    $locale . '.UTF8',
                    $locale . '.ISO8859-1',
                    $locale,
                    $language,
                    setlocale(LC_ALL, 0) // fall back to the previously defined locale
                ];

                // set the locales that are relevant for string formatting
                // *don't* set LC_CTYPE to avoid breaking other parts of the system
                setlocale(LC_MONETARY, $locales);
                setlocale(LC_NUMERIC, $locales);
                setlocale(LC_TIME, $locales);
            }
        }

        // don't throw pagination errors if pagination
        // page is out of bounds
        $validate = Pagination::$validate;
        Pagination::$validate = false;

        $output = $this->route->action()->call($this, ...$this->route->arguments());

        // restore old pagination validation mode
        Pagination::$validate = $validate;

        if (
            is_object($output) === true &&
            is_a($output, 'Kirby\\Http\\Response') !== true
        ) {
            return $this->resolve($output)->toResponse();
        }

        return $output;
    }

    /**
     * Setter and getter for an API collection
     *
     * @param string $name
     * @param array|null $collection
     * @return \Kirby\Api\Collection
     * @throws \Kirby\Exception\NotFoundException If no collection for `$name` exists
     * @throws \Exception
     */
    public function collection(string $name, $collection = null)
    {
        if (isset($this->collections[$name]) === false) {
            throw new NotFoundException(sprintf('The collection "%s" does not exist', $name));
        }

        return new Collection($this, $collection, $this->collections[$name]);
    }

    /**
     * Returns the collections definition
     *
     * @return array
     */
    public function collections(): array
    {
        return $this->collections;
    }

    /**
     * Returns the injected data array
     * or certain parts of it by key
     *
     * @param string|null $key
     * @param mixed ...$args
     * @return mixed
     *
     * @throws \Kirby\Exception\NotFoundException If no data for `$key` exists
     */
    public function data($key = null, ...$args)
    {
        if ($key === null) {
            return $this->data;
        }

        if ($this->hasData($key) === false) {
            throw new NotFoundException(sprintf('Api data for "%s" does not exist', $key));
        }

        // lazy-load data wrapped in Closures
        if (is_a($this->data[$key], 'Closure') === true) {
            return $this->data[$key]->call($this, ...$args);
        }

        return $this->data[$key];
    }

    /**
     * Returns the debugging flag
     *
     * @return bool
     */
    public function debug(): bool
    {
        return $this->debug;
    }

    /**
     * Checks if injected data exists for the given key
     *
     * @param string $key
     * @return bool
     */
    public function hasData(string $key): bool
    {
        return isset($this->data[$key]) === true;
    }

    /**
     * Matches an object with an array item
     * based on the `type` field
     *
     * @param array models or collections
     * @param mixed $object
     *
     * @return string key of match
     */
    protected function match(array $array, $object = null)
    {
        foreach ($array as $definition => $model) {
            if (is_a($object, $model['type']) === true) {
                return $definition;
            }
        }
    }

    /**
     * Returns an API model instance by name
     *
     * @param string|null $name
     * @param mixed $object
     * @return \Kirby\Api\Model
     *
     * @throws \Kirby\Exception\NotFoundException If no model for `$name` exists
     */
    public function model(string $name = null, $object = null)
    {
        // Try to auto-match object with API models
        if ($name === null) {
            if ($model = $this->match($this->models, $object)) {
                $name = $model;
            }
        }

        if (isset($this->models[$name]) === false) {
            throw new NotFoundException(sprintf('The model "%s" does not exist', $name));
        }

        return new Model($this, $object, $this->models[$name]);
    }

    /**
     * Returns all model definitions
     *
     * @return array
     */
    public function models(): array
    {
        return $this->models;
    }

    /**
     * Getter for request data
     * Can either get all the data
     * or certain parts of it.
     *
     * @param string|null $type
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function requestData(string $type = null, string $key = null, $default = null)
    {
        if ($type === null) {
            return $this->requestData;
        }

        if ($key === null) {
            return $this->requestData[$type] ?? [];
        }

        $data = array_change_key_case($this->requestData($type));
        $key  = strtolower($key);

        return $data[$key] ?? $default;
    }

    /**
     * Returns the request body if available
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function requestBody(string $key = null, $default = null)
    {
        return $this->requestData('body', $key, $default);
    }

    /**
     * Returns the files from the request if available
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function requestFiles(string $key = null, $default = null)
    {
        return $this->requestData('files', $key, $default);
    }

    /**
     * Returns all headers from the request if available
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function requestHeaders(string $key = null, $default = null)
    {
        return $this->requestData('headers', $key, $default);
    }

    /**
     * Returns the request method
     *
     * @return string
     */
    public function requestMethod(): string
    {
        return $this->requestMethod;
    }

    /**
     * Returns the request query if available
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function requestQuery(string $key = null, $default = null)
    {
        return $this->requestData('query', $key, $default);
    }

    /**
     * Turns a Kirby object into an
     * API model or collection representation
     *
     * @param mixed $object
     * @return \Kirby\Api\Model|\Kirby\Api\Collection
     *
     * @throws \Kirby\Exception\NotFoundException If `$object` cannot be resolved
     */
    public function resolve($object)
    {
        if (is_a($object, 'Kirby\Api\Model') === true || is_a($object, 'Kirby\Api\Collection') === true) {
            return $object;
        }

        if ($model = $this->match($this->models, $object)) {
            return $this->model($model, $object);
        }

        if ($collection = $this->match($this->collections, $object)) {
            return $this->collection($collection, $object);
        }

        throw new NotFoundException(sprintf('The object "%s" cannot be resolved', get_class($object)));
    }

    /**
     * Returns all defined routes
     *
     * @return array
     */
    public function routes(): array
    {
        return $this->routes;
    }

    /**
     * Setter for the authentication callback
     *
     * @param \Closure|null $authentication
     * @return $this
     */
    protected function setAuthentication(Closure $authentication = null)
    {
        $this->authentication = $authentication;
        return $this;
    }

    /**
     * Setter for the collections definition
     *
     * @param array|null $collections
     * @return $this
     */
    protected function setCollections(array $collections = null)
    {
        if ($collections !== null) {
            $this->collections = array_change_key_case($collections);
        }
        return $this;
    }

    /**
     * Setter for the injected data
     *
     * @param array|null $data
     * @return $this
     */
    protected function setData(array $data = null)
    {
        $this->data = $data ?? [];
        return $this;
    }

    /**
     * Setter for the debug flag
     *
     * @param bool $debug
     * @return $this
     */
    protected function setDebug(bool $debug = false)
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * Setter for the model definitions
     *
     * @param array|null $models
     * @return $this
     */
    protected function setModels(array $models = null)
    {
        if ($models !== null) {
            $this->models = array_change_key_case($models);
        }

        return $this;
    }

    /**
     * Setter for the request data
     *
     * @param array|null $requestData
     * @return $this
     */
    protected function setRequestData(array $requestData = null)
    {
        $defaults = [
            'query' => [],
            'body'  => [],
            'files' => []
        ];

        $this->requestData = array_merge($defaults, (array)$requestData);
        return $this;
    }

    /**
     * Setter for the request method
     *
     * @param string|null $requestMethod
     * @return $this
     */
    protected function setRequestMethod(string $requestMethod = null)
    {
        $this->requestMethod = $requestMethod ?? 'GET';
        return $this;
    }

    /**
     * Setter for the route definitions
     *
     * @param array|null $routes
     * @return $this
     */
    protected function setRoutes(array $routes = null)
    {
        $this->routes = $routes ?? [];
        return $this;
    }

    /**
     * Renders the API call
     *
     * @param string $path
     * @param string $method
     * @param array $requestData
     * @return mixed
     */
    public function render(string $path, $method = 'GET', array $requestData = [])
    {
        try {
            $result = $this->call($path, $method, $requestData);
        } catch (Throwable $e) {
            $result = $this->responseForException($e);
        }

        if ($result === null) {
            $result = $this->responseFor404();
        } elseif ($result === false) {
            $result = $this->responseFor400();
        } elseif ($result === true) {
            $result = $this->responseFor200();
        }

        if (is_array($result) === false) {
            return $result;
        }

        // pretty print json data
        $pretty = (bool)($requestData['query']['pretty'] ?? false) === true;

        if (($result['status'] ?? 'ok') === 'error') {
            $code = $result['code'] ?? 400;

            // sanitize the error code
            if ($code < 400 || $code > 599) {
                $code = 500;
            }

            return Response::json($result, $code, $pretty);
        }

        return Response::json($result, 200, $pretty);
    }

    /**
     * Returns a 200 - ok
     * response array.
     *
     * @return array
     */
    public function responseFor200(): array
    {
        return [
            'status'  => 'ok',
            'message' => 'ok',
            'code'    => 200
        ];
    }

    /**
     * Returns a 400 - bad request
     * response array.
     *
     * @return array
     */
    public function responseFor400(): array
    {
        return [
            'status'  => 'error',
            'message' => 'bad request',
            'code'    => 400,
        ];
    }

    /**
     * Returns a 404 - not found
     * response array.
     *
     * @return array
     */
    public function responseFor404(): array
    {
        return [
            'status'  => 'error',
            'message' => 'not found',
            'code'    => 404,
        ];
    }

    /**
     * Creates the response array for
     * an exception. Kirby exceptions will
     * have more information
     *
     * @param \Throwable $e
     * @return array
     */
    public function responseForException(Throwable $e): array
    {
        // prepare the result array for all exception types
        $result = [
            'status'    => 'error',
            'message'   => $e->getMessage(),
            'code'      => empty($e->getCode()) === true ? 500 : $e->getCode(),
            'exception' => get_class($e),
            'key'       => null,
            'file'      => F::relativepath($e->getFile(), $_SERVER['DOCUMENT_ROOT'] ?? null),
            'line'      => $e->getLine(),
            'details'   => [],
            'route'     => $this->route ? $this->route->pattern() : null
        ];

        // extend the information for Kirby Exceptions
        if (is_a($e, 'Kirby\Exception\Exception') === true) {
            $result['key']     = $e->getKey();
            $result['details'] = $e->getDetails();
            $result['code']    = $e->getHttpCode();
        }

        // remove critical info from the result set if
        // debug mode is switched off
        if ($this->debug !== true) {
            unset(
                $result['file'],
                $result['exception'],
                $result['line'],
                $result['route']
            );
        }

        return $result;
    }

    /**
     * Upload helper method
     *
     * move_uploaded_file() not working with unit test
     * Added debug parameter for testing purposes as we did in the Email class
     *
     * @param \Closure $callback
     * @param bool $single
     * @param bool $debug
     * @return array
     *
     * @throws \Exception If request has no files or there was an error with the upload
     */
    public function upload(Closure $callback, $single = false, $debug = false): array
    {
        $trials  = 0;
        $uploads = [];
        $errors  = [];
        $files   = $this->requestFiles();

        // get error messages from translation
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE   => t('upload.error.iniSize'),
            UPLOAD_ERR_FORM_SIZE  => t('upload.error.formSize'),
            UPLOAD_ERR_PARTIAL    => t('upload.error.partial'),
            UPLOAD_ERR_NO_FILE    => t('upload.error.noFile'),
            UPLOAD_ERR_NO_TMP_DIR => t('upload.error.tmpDir'),
            UPLOAD_ERR_CANT_WRITE => t('upload.error.cantWrite'),
            UPLOAD_ERR_EXTENSION  => t('upload.error.extension')
        ];

        if (empty($files) === true) {
            $postMaxSize       = Str::toBytes(ini_get('post_max_size'));
            $uploadMaxFileSize = Str::toBytes(ini_get('upload_max_filesize'));

            if ($postMaxSize < $uploadMaxFileSize) {
                throw new Exception(t('upload.error.iniPostSize'));
            } else {
                throw new Exception(t('upload.error.noFiles'));
            }
        }

        foreach ($files as $upload) {
            if (isset($upload['tmp_name']) === false && is_array($upload)) {
                continue;
            }

            $trials++;

            try {
                if ($upload['error'] !== 0) {
                    $errorMessage = $errorMessages[$upload['error']] ?? t('upload.error.default');
                    throw new Exception($errorMessage);
                }

                // get the extension of the uploaded file
                $extension = F::extension($upload['name']);

                // try to detect the correct mime and add the extension
                // accordingly. This will avoid .tmp filenames
                if (empty($extension) === true || in_array($extension, ['tmp', 'temp'])) {
                    $mime      = F::mime($upload['tmp_name']);
                    $extension = F::mimeToExtension($mime);
                    $filename  = F::name($upload['name']) . '.' . $extension;
                } else {
                    $filename = basename($upload['name']);
                }

                $source = dirname($upload['tmp_name']) . '/' . uniqid() . '.' . $filename;

                // move the file to a location including the extension,
                // for better mime detection
                if ($debug === false && move_uploaded_file($upload['tmp_name'], $source) === false) {
                    throw new Exception(t('upload.error.cantMove'));
                }

                $data = $callback($source, $filename);

                if (is_object($data) === true) {
                    $data = $this->resolve($data)->toArray();
                }

                $uploads[$upload['name']] = $data;
            } catch (Exception $e) {
                $errors[$upload['name']] = $e->getMessage();
            }

            if ($single === true) {
                break;
            }
        }

        // return a single upload response
        if ($trials === 1) {
            if (empty($errors) === false) {
                return [
                    'status'  => 'error',
                    'message' => current($errors)
                ];
            }

            return [
                'status' => 'ok',
                'data'   => current($uploads)
            ];
        }

        if (empty($errors) === false) {
            return [
                'status' => 'error',
                'errors' => $errors
            ];
        }

        return [
            'status' => 'ok',
            'data'   => $uploads
        ];
    }
}
