import type {
	GutenStyleBlockAttributes,
	ScopeStyle,
	StyleValue,
} from '../types/style-engine';

export const GUTENSTYLE_ATTRIBUTE_KEY = 'gutenstyle' as const;

export const emptyScopeStyle = (): ScopeStyle => ( {
	version: 1,
	preset: null,
	properties: {},
} );

export const resetBlockProperty = (
	attributes: GutenStyleBlockAttributes,
	propertyId: string
): GutenStyleBlockAttributes => {
	if ( ! attributes.gutenstyle ) {
		return attributes;
	}

	const properties = { ...attributes.gutenstyle.properties };
	delete properties[ propertyId ];

	if (
		attributes.gutenstyle.preset === null &&
		Object.keys( properties ).length === 0
	) {
		const { gutenstyle: removed, ...rest } = attributes;
		void removed;
		return rest;
	}

	return {
		...attributes,
		gutenstyle: { ...attributes.gutenstyle, properties },
	};
};

export const setBlockProperty = (
	attributes: GutenStyleBlockAttributes,
	propertyId: string,
	value: StyleValue
): GutenStyleBlockAttributes => {
	const style = attributes.gutenstyle ?? emptyScopeStyle();

	return {
		...attributes,
		gutenstyle: {
			...style,
			properties: { ...style.properties, [ propertyId ]: value },
		},
	};
};

export const resetAllBlockStyles = (
	attributes: GutenStyleBlockAttributes
): GutenStyleBlockAttributes => {
	const { gutenstyle: removed, ...rest } = attributes;
	void removed;
	return rest;
};
