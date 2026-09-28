import type {
	EffectiveStyle,
	PresetDefinition,
	PropertyDefinition,
	ResolvedProperty,
	ScopeStyle,
	StyleOrigin,
} from '../types/style-engine';

interface ResolverRegistry {
	properties: Record< string, PropertyDefinition >;
	presets: Record< string, PresetDefinition >;
}

interface ResolverScopes {
	global?: ScopeStyle;
	post?: ScopeStyle;
	block?: ScopeStyle;
}

const unresolved = (): ResolvedProperty => ( {
	value: null,
	source: 'theme',
	via: 'none',
	preset: null,
} );

/**
 * Client-side mirror used only for immediate previews and origin labels.
 * WordPress/PHP remains authoritative when values are saved or rendered.
 * @param module
 * @param registry
 * @param scopes
 */
export const resolveStyle = (
	module: string,
	registry: ResolverRegistry,
	scopes: ResolverScopes
): EffectiveStyle => {
	const properties: Record< string, ResolvedProperty > = {};
	Object.values( registry.properties ).forEach( ( definition ) => {
		if (
			definition.modules.length === 0 ||
			definition.modules.includes( module )
		) {
			properties[ definition.id ] = unresolved();
		}
	} );

	let effectivePreset: string | null = null;
	let presetSource: StyleOrigin = 'theme';
	( [ 'global', 'post', 'block' ] as const ).forEach( ( source ) => {
		const scope = scopes[ source ];
		if ( ! scope ) {
			return;
		}

		if ( scope.preset ) {
			const preset = registry.presets[ scope.preset ];
			if ( preset?.module === module ) {
				Object.entries( preset.properties ).forEach(
					( [ propertyId, value ] ) => {
						if ( properties[ propertyId ] ) {
							properties[ propertyId ] = {
								value,
								source,
								via: 'preset',
								preset: preset.id,
							};
						}
					}
				);
				effectivePreset = preset.id;
				presetSource = source;
			}
		}

		Object.entries( scope.properties ).forEach(
			( [ propertyId, value ] ) => {
				if ( properties[ propertyId ] ) {
					properties[ propertyId ] = {
						value,
						source,
						via: 'property',
						preset: null,
					};
				}
			}
		);
	} );

	return {
		module,
		preset: effectivePreset,
		presetSource,
		properties,
	};
};
