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

const response = await php.run( {
	code: `<?php
$paths = [ 'gutenstyle.php', 'includes', 'tests/php' ];
$files = [];
foreach ( $paths as $path ) {
	if ( is_file( $path ) ) {
		$files[] = $path;
		continue;
	}
	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $path, FilesystemIterator::SKIP_DOTS )
	);
	foreach ( $iterator as $file ) {
		if ( $file->isFile() && 'php' === strtolower( $file->getExtension() ) ) {
			$files[] = $file->getPathname();
		}
	}
}
sort( $files );
$failed = 0;
foreach ( $files as $file ) {
	try {
		token_get_all( file_get_contents( $file ), TOKEN_PARSE );
	} catch ( ParseError $error ) {
		++$failed;
		echo "FAIL: {$file} - {$error->getMessage()}\\n";
	}
}
if ( 0 === $failed ) {
	echo count( $files ) . " PHP files passed syntax validation.\\n";
}
exit( 0 === $failed ? 0 : 1 );`,
} );

if ( response.text ) {
	process.stdout.write( response.text );
}
if ( response.errors ) {
	process.stderr.write( response.errors );
}
process.exitCode = response.exitCode;
