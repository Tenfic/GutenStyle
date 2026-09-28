const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
	...defaultConfig,
	entry: {
		admin: path.resolve(process.cwd(), 'src/admin/index.tsx'),
		editor: path.resolve(process.cwd(), 'src/editor/index.tsx'),
		table: path.resolve(process.cwd(), 'src/styles/table.css'),
	},
};
