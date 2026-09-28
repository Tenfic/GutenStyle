import { PHP } from '@php-wasm/universal';
import { createNodeFsMountHandler, loadNodeRuntime } from '@php-wasm/node';

const php = new PHP(
	await loadNodeRuntime( '7.4', {
		emscriptenOptions: { processId: process.pid },
	} )
);
php.mkdirTree( '/workspace' );
await php.mount( '/workspace', createNodeFsMountHandler( process.cwd() ) );
php.chdir( '/workspace' );

try {
	const response = await php.run( {
		code: "<?php require 'tests/php/run.php';",
	} );

	if ( response.text ) {
		process.stdout.write( response.text );
	}
	if ( response.errors ) {
		process.stderr.write( response.errors );
	}
	process.exitCode = response.exitCode;
} finally {
	php[ Symbol.dispose ]();
}
