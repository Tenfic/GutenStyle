import apiFetch from '@wordpress/api-fetch';
import { useEffect, useState } from '@wordpress/element';
import type { StyleDocument, StyleSchema } from '../types/style-engine';

interface TableData {
	schema: StyleSchema;
	global: StyleDocument;
}

let cached: TableData | null = null;
let request: Promise< TableData > | null = null;

const load = (): Promise< TableData > => {
	if ( cached ) {
		return Promise.resolve( cached );
	}
	if ( ! request ) {
		request = Promise.all( [
			apiFetch< StyleSchema >( { path: '/gutenstyle/v1/schema' } ),
			apiFetch< StyleDocument >( {
				path: '/gutenstyle/v1/styles/global',
			} ),
		] ).then( ( [ schema, global ] ) => {
			cached = { schema, global };
			return cached;
		} );
	}
	return request;
};

export const useTableData = () => {
	const [ data, setData ] = useState< TableData | null >( cached );
	const [ error, setError ] = useState< string | null >( null );

	useEffect( () => {
		if ( data ) {
			return;
		}
		load()
			.then( setData )
			.catch( ( reason: unknown ) =>
				setError(
					reason instanceof Error
						? reason.message
						: 'GutenStyle data could not be loaded.'
				)
			);
	}, [ data ] );

	return { data, error };
};
