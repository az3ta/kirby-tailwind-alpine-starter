import * as THREE from './three.module.js'
import { PointerLockControls } from './controls/PointerLockControls.js'
import { OrbitControls } from './controls/OrbitControls.js'
import { FirstPersonControls } from './controls/FirstPersonControls.js'

let container, tooltip, article
let camera, scene, raycaster, renderer, controls

let INTERSECTED
let theta = 0
const links = []
const pointer = new THREE.Vector2()
const radius = 100
const current_mouse_position = { x: 0, y: 0 }
let moveForward = false
let moveBackward = false
let moveLeft = false
let moveRight = false
let canJump = false

let prevTime = performance.now()
const velocity = new THREE.Vector3()
const direction = new THREE.Vector3()

const cubes = []

let ignore_mouse_down = false
let ignore_navigation = false
let article_position = new THREE.Vector3()
let article_quaternion = new THREE.Quaternion()
let start_position = new THREE.Vector3()
let start_quaternion = new THREE.Quaternion()
let current_index
let page_built = false
let animation_to_article = false
let animation_to_start = false
let rendered_link_disposed = false

init()
animate()

function init() {
	tooltip = document.querySelector('.tooltip')
	article = document.querySelector('.article')

	container = document.createElement('div')
	document.body.appendChild(container)

	// camera
	camera = new THREE.PerspectiveCamera(
		70,
		window.innerWidth / window.innerHeight,
		1,
		10000
	)
	camera.position.z = 30

	scene = new THREE.Scene()
	scene.background = null
	scene.fog = new THREE.Fog(0xb1c7c9, 50, 800)
	// scene.autoUpdate = false;

	// const light = new THREE.DirectionalLight(0xffffff, 1);
	// const light = new THREE.AmbientLight(0x404040);
	const light = new THREE.HemisphereLight(0xe5ca85, 0xb1c7c9, 1)
	light.position.set(1, 1, 1).normalize()
	scene.add(light)

	const geometry = new THREE.BoxGeometry(20, 20, 20)

	for (let i = 0; i < 200; i++) {
		// build fake links to test
		const link = {
			title: 'Link ' + i,
			description: 'This is a link for content ' + i
		}
		links.push(link)

		const object = new THREE.Mesh(
			geometry,
			new THREE.MeshLambertMaterial({ color: Math.random() * 0xffffff })
		)

		object['iinnddeexx'] = i

		object.position.x = Math.random() * 800 - 400
		object.position.y = Math.random() * 800 - 400
		object.position.z = Math.random() * 800 - 400

		// object.rotation.x = Math.random() * 2 * Math.PI;
		// object.rotation.y = Math.random() * 2 * Math.PI;
		// object.rotation.z = Math.random() * 2 * Math.PI;

		object.scale.x = Math.random() + 0.5
		object.scale.y = Math.random() + 0.5
		object.scale.z = Math.random() + 0.5

		cubes.push(object)

		scene.add(object)
	}

	raycaster = new THREE.Raycaster()

	// transparent canvas!
	renderer = new THREE.WebGLRenderer({ alpha: true })
	renderer.setClearColor(0x000000, 0)
	renderer.setPixelRatio(window.devicePixelRatio)
	renderer.setSize(window.innerWidth, window.innerHeight)
	container.appendChild(renderer.domElement)

	init_controls()

	document.addEventListener('mousemove', onPointerMove)
	// ignore mouse while pointing with mouse
	document.addEventListener('mousedown', () => (ignore_mouse_down = true))
	document.addEventListener('mouseup', () => (ignore_mouse_down = false))

	//

	window.addEventListener('resize', onWindowResize)
}

function init_controls() {
	controls = new FirstPersonControls(camera, renderer.domElement)
	controls.setLookSpeed(0.15)
	controls.movementSpeed = 20
}

function onWindowResize() {
	camera.aspect = window.innerWidth / window.innerHeight
	camera.updateProjectionMatrix()

	renderer.setSize(window.innerWidth, window.innerHeight)
}

function onPointerMove(event) {
	pointer.x = (event.clientX / window.innerWidth) * 2 - 1
	pointer.y = -(event.clientY / window.innerHeight) * 2 + 1
	current_mouse_position.x = event.clientX
	current_mouse_position.y = event.clientY

	tooltip.style.top = `${event.clientY}px`
	tooltip.style.left = `${event.clientX}px`
}

//

function animate() {
	requestAnimationFrame(animate)

	// controls.update();
	update_movement()
	render()
	// stats.update();
}

function update_movement() {
	const time = performance.now()

	// raycaster.ray.origin.copy(controls.getObject().position);
	// raycaster.ray.origin.y -= 10;

	const delta = (time - prevTime) / 1000

	if (!ignore_navigation) {
		controls.update(delta)
	} else {
		// if (animation_to_start) {
		//   console.log('animation to start');
		//   const target_position = start_position;
		//   // const target_position = new THREE.Vector3(0, 0, 0);
		//   camera.position.lerp(target_position, 0.01);
		//   camera.quaternion.slerp(start_quaternion, 0.01);
		//   const distance_to_target = camera.position.distanceTo(target_position);
		//   if (distance_to_target < 0.5) {
		//     console.log('target reached');
		//     animation_to_start = false;
		//     ignore_navigation = false;
		//     init_controls()
		//   }
		//   reset_booleans();
		// } else {
		const target_position = new THREE.Vector3()
		target_position.addVectors(article_position, new THREE.Vector3(0, 0, 50))
		camera.position.lerp(target_position, 0.025)
		camera.quaternion.slerp(article_quaternion, 0.025)
		const distance_to_target = camera.position.distanceTo(target_position)
		if (distance_to_target < 1.5 && !page_built) {
			build_page(current_index)
			page_built = true
			// controls.update(delta)
		}
		// }
	}

	prevTime = time
}

function render() {
	const dummy = new THREE.Vector3()
	controls.set_camera_direction(camera.getWorldDirection(dummy))

	const origin = new THREE.Vector2(0, 0)
	// raycaster.setFromCamera(origin, camera);
	raycaster.setFromCamera(pointer, camera)

	const intersects = raycaster.intersectObjects(scene.children, false)

	if (intersects.length > 0) {
		if (INTERSECTED != intersects[0].object) {
			if (INTERSECTED)
				INTERSECTED.material.emissive.setHex(INTERSECTED.currentHex)

			INTERSECTED = intersects[0].object
			INTERSECTED.currentHex = INTERSECTED.material.emissive.getHex()
			INTERSECTED.material.emissive.setHex(0xff0000)
			// if mouse is in use to navigate do not show tooltip
			if (!ignore_mouse_down) {
				// as long as the the article is not closed we ignore the following
				if (!animation_to_article) {
					article_position = INTERSECTED.position
					article_quaternion = INTERSECTED.quaternion
					start_quaternion = camera.quaternion
					// start_position = camera.position;
					current_index = INTERSECTED['iinnddeexx']
				}
				rendered_link_disposed = false
				render_link(INTERSECTED['iinnddeexx'])
			}
		}
	} else {
		if (INTERSECTED)
			INTERSECTED.material.emissive.setHex(INTERSECTED.currentHex)

		INTERSECTED = null
		if (!rendered_link_disposed) {
			dispose_rendered_link()
		}
	}

	for (let i = 0; i < cubes.length; i++) {
		const cube = cubes[i]
		cube.lookAt(camera.position)
	}

	renderer.render(scene, camera)
}

function animate_camera_to_article() {
	animation_to_article = true
	ignore_navigation = true
}

function animate_camera_to_start() {
	animation_to_start = true
}

function render_link(index) {
	tooltip.innerHTML = ''
	tooltip.style.display = 'flex'
	build_tooltip(index)
}
function dispose_rendered_link() {
	// console.log('object disposed');
	tooltip.style.display = 'none'
	rendered_link_disposed = true
}
function build_tooltip(index) {
	const link = links[index]
	const { title, description } = link
	const click_element = document.createElement('div')
	click_element.textContent = title
	click_element.addEventListener('click', () => {
		animate_camera_to_article()
		dispose_rendered_link()
	})
	tooltip.appendChild(click_element)
}

function build_page(index) {
	console.log('build page')
	const link = links[index]
	const { title, description } = link
	const page = document.createElement('div')
	page.classList.add('page')
	const title_element = document.createElement('div')
	title_element.classList.add('page-title')
	title_element.textContent = title
	const description_element = document.createElement('div')
	description_element.classList.add('page-content')
	description_element.textContent = description

	// build close button and add to page
	const close_element = document.createElement('div')
	close_element.classList.add('page-close')
	close_element.textContent = 'X CLOSE X'
	close_element.addEventListener('click', () => {
		animate_camera_to_start()
		dispose_article()
	})

	page.appendChild(title_element)
	page.appendChild(description_element)
	page.appendChild(close_element)
	article.appendChild(page)
	article.style.display = 'flex'
}

function dispose_article() {
	console.log('dispose article')
	article.style.display = 'none'
	article.innerHTML = ''
	reset_camera()
}

function reset_camera() {
	init_controls()
	reset_booleans()
}

function reset_booleans() {
	// reset all the booleans
	page_built = false
	animation_to_article = false
	ignore_navigation = false
	ignore_mouse_down = false
}
