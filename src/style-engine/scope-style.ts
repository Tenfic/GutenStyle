import type {
	ScopeStyle,
	StyleDocument,
	StyleValue,
} from '../types/style-engine';

export const EMPTY_STYLE_DOCUMENT: StyleDocument = {
	version: 1,
	blocks: {},
};

export const createScopeStyle = (): ScopeStyle => ( {
	version: 1,
	preset: null,
	properties: {},
} );

export const isScopeStyleEmpty = ( style: ScopeStyle ): boolean =>
	style.preset === null && Object.keys( style.properties ).length === 0;

export const getModuleStyle = (
	document: StyleDocument,
	module: string
): ScopeStyle => document.blocks[ module ] ?? createScopeStyle();

export const setModuleStyle = (
	document: StyleDocument,
	module: string,
	style: ScopeStyle
): StyleDocument => {
	const blocks = { ...document.blocks };
	if ( isScopeStyleEmpty( style ) ) {
		delete blocks[ module ];
	} else {
		blocks[ module ] = style;
	}

	return { version: 1, blocks };
};

export const setScopePreset = (
	style: ScopeStyle,
	preset: string | null
): ScopeStyle => ( { ...style, preset } );

export const setScopeProperty = (
	style: ScopeStyle,
	propertyId: string,
	value: StyleValue
): ScopeStyle => ( {
	...style,
	properties: { ...style.properties, [ propertyId ]: value },
} );

export const resetScopeProperty = (
	style: ScopeStyle,
	propertyId: string
): ScopeStyle => {
	const properties = { ...style.properties };
	delete properties[ propertyId ];
	return { ...style, properties };
};
