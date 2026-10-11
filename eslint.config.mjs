import js from '@eslint/js';

export default [
	js.configs.recommended,
	{
		files: [ 'src/js/**/*.js' ],
		languageOptions: {
			ecmaVersion: 5,
			sourceType: 'script',
			globals: {
				jQuery: 'readonly',
				window: 'readonly',
				document: 'readonly',
			},
		},
	},
];
