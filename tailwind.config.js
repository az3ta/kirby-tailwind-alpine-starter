const defaultTheme = require('tailwindcss/defaultTheme')

module.exports = {
	mode: 'jit',
	content: ['./site/**/*.php'],
	theme: {
		extend: {},
		fontFamily: {
			sans: ['Arial', ...defaultTheme.fontFamily.sans]
		}
	},
	plugins: []
}
