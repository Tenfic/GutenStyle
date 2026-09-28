import type { CSSProperties } from 'react';
import type {
	BlockAdapterDefinition,
	EffectiveStyle,
} from '../types/style-engine';

export const TABLE_MODULE = 'core/table';
export const THEME_DEFAULT_PRESET = 'table-theme-default';
export const TABLE_WRAPPER_CLASS = 'gutenstyle-table';

export const TABLE_PROPERTY_GROUPS: Record< string, string[] > = {
	Container: [
		'tableBorderStyle',
		'tableBorderWidth',
		'tableBorderColor',
		'tableBorderRadius',
		'tableBackgroundColor',
	],
	Header: [
		'headerBackgroundColor',
		'headerTextColor',
		'headerFontWeight',
		'headerTextAlign',
	],
	Cells: [
		'cellPaddingHorizontal',
		'cellPaddingVertical',
		'cellTextColor',
		'cellBackgroundColor',
		'cellTextAlign',
		'cellVerticalAlign',
	],
	Rows: [
		'stripedRows',
		'stripedRowBackgroundColor',
		'hoverRows',
		'hoverRowBackgroundColor',
		'dividerColor',
	],
	Caption: [
		'captionTextColor',
		'captionFontSize',
		'captionTextAlign',
		'captionSpacingTop',
		'captionSpacingBottom',
	],
	Responsive: [ 'tableResponsiveMode' ],
};

type CustomProperties = CSSProperties & Record< `--${ string }`, string >;

export interface TablePresentation {
	className: string;
	wrapperStyle: CustomProperties;
	tableStyle: CSSProperties;
}

const propertyClass = ( propertyId: string ): string =>
	`${ TABLE_WRAPPER_CLASS }--has-${ propertyId
		.replace( /([a-z0-9])([A-Z])/g, '$1-$2' )
		.toLowerCase() }`;

const nativeCssProperties: Record< string, keyof CSSProperties > = {
	'border.style': 'borderStyle',
	'border.width': 'borderWidth',
	'border.color': 'borderColor',
	'color.background': 'backgroundColor',
	'color.text': 'color',
};

const nativePreviewVariables: Record< string, `--${ string }` > = {
	'border.style': '--gutenstyle-table-native-border-style',
	'border.width': '--gutenstyle-table-native-border-width',
	'border.color': '--gutenstyle-table-native-border-color',
	'color.background': '--gutenstyle-table-native-background',
	'color.text': '--gutenstyle-table-native-text-color',
};

export const getNativeTableOverrides = (
	attributes: Record< string, unknown >
): Set< string > => {
	const style = ( attributes.style ?? {} ) as Record< string, unknown >;
	const border = ( style.border ?? {} ) as Record< string, unknown >;
	const color = ( style.color ?? {} ) as Record< string, unknown >;
	const typography = ( style.typography ?? {} ) as Record< string, unknown >;
	const excluded = new Set< string >();

	if ( border.style !== undefined ) {
		excluded.add( 'tableBorderStyle' );
	}
	if ( border.width !== undefined ) {
		excluded.add( 'tableBorderWidth' );
	}
	if ( border.color !== undefined || attributes.borderColor ) {
		excluded.add( 'tableBorderColor' );
		excluded.add( 'dividerColor' );
	}
	if (
		color.background !== undefined ||
		color.gradient !== undefined ||
		attributes.backgroundColor ||
		attributes.gradient
	) {
		excluded.add( 'tableBackgroundColor' );
	}
	if ( color.text !== undefined || attributes.textColor ) {
		excluded.add( 'cellTextColor' );
		excluded.add( 'headerTextColor' );
	}
	if ( typography.fontWeight !== undefined ) {
		excluded.add( 'headerFontWeight' );
	}
	if ( typography.fontSize !== undefined || attributes.fontSize ) {
		excluded.add( 'captionFontSize' );
	}
	if (
		typeof attributes.className === 'string' &&
		attributes.className.split( /\s+/ ).includes( 'is-style-stripes' )
	) {
		excluded.add( 'stripedRows' );
		excluded.add( 'stripedRowBackgroundColor' );
	}

	return excluded;
};

export const compileTablePresentation = (
	effective: EffectiveStyle,
	adapter: BlockAdapterDefinition,
	excluded = new Set< string >()
): TablePresentation => {
	const classes = new Set< string >( [ TABLE_WRAPPER_CLASS ] );
	const wrapperStyle = {} as CustomProperties;
	const tableStyle: CSSProperties = {};

	Object.entries( effective.properties ).forEach(
		( [ propertyId, resolved ] ) => {
			if ( resolved.value === null || excluded.has( propertyId ) ) {
				return;
			}

			const mapping = adapter.properties[ propertyId ];
			if ( mapping?.cssVariable ) {
				wrapperStyle[ mapping.cssVariable as `--${ string }` ] = String(
					resolved.value
				);
				classes.add( propertyClass( propertyId ) );
			}

			if ( mapping?.styleEnginePath ) {
				const path = mapping.styleEnginePath.join( '.' );
				const cssProperty = nativeCssProperties[ path ];
				if ( cssProperty ) {
					( tableStyle as Record< string, string > )[ cssProperty ] =
						String( resolved.value );
					wrapperStyle[ nativePreviewVariables[ path ] ] = String(
						resolved.value
					);
					classes.add( propertyClass( propertyId ) );
				}
			}
		}
	);

	if (
		effective.properties.stripedRows?.value === true &&
		! excluded.has( 'stripedRows' )
	) {
		classes.add( `${ TABLE_WRAPPER_CLASS }--striped` );
	}
	if (
		effective.properties.hoverRows?.value === true &&
		! excluded.has( 'hoverRows' )
	) {
		classes.add( `${ TABLE_WRAPPER_CLASS }--hover` );
	}
	if (
		effective.properties.tableResponsiveMode?.value ===
			'horizontal-scroll' &&
		! excluded.has( 'tableResponsiveMode' )
	) {
		classes.add( `${ TABLE_WRAPPER_CLASS }--horizontal-scroll` );
	}

	return {
		className: Array.from( classes ).join( ' ' ),
		wrapperStyle,
		tableStyle,
	};
};
