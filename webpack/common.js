const path = require( 'path' );
const webpack = require( 'webpack' );
const resolve = require( './resolve' );

module.exports = {
	externals: {
		jquery: 'jQuery',
	},
	resolve,
	resolveLoader: {
		modules: [
			path.resolve( `${ __dirname }/../`, 'node_modules' ),
		],
	},
	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: [ /(node_modules)/ ],
				use: [
					{
						loader: 'babel-loader',
					},
				],
			},
		],
	},
	plugins: [
		new webpack.IgnorePlugin( /^\.\/locale$/, /moment$/ ),
		new webpack.ProvidePlugin( {
			jQuery: 'jquery',
			$: 'jquery',
		} ),
	],
};
