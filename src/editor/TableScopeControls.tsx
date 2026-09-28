import {
	Button,
	Notice,
	PanelBody,
	SelectControl,
	TextControl,
} from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import type { KeyboardEvent } from 'react';
import {
	resetScopeProperty,
	setScopePreset,
	setScopeProperty,
} from '../style-engine/scope-style';
import {
	TABLE_PROPERTY_GROUPS,
	THEME_DEFAULT_PRESET,
} from '../table/table-style';
import type {
	EffectiveStyle,
	PresetDefinition,
	PropertyDefinition,
	ScopeStyle,
	StyleValue,
} from '../types/style-engine';

interface TableScopeControlsProps {
	scopeLabel: 'Post' | 'Block';
	scope: ScopeStyle;
	effective: EffectiveStyle;
	definitions: Record< string, PropertyDefinition >;
	presets: PresetDefinition[];
	onChange: ( style: ScopeStyle ) => void;
	onResetAll: () => void;
}

const sourceLabel = ( source: string ): string => {
	if ( source === 'theme' ) {
		return 'Theme/Core';
	}
	if ( source === 'post' ) {
		return 'Post Style';
	}
	return source[ 0 ].toUpperCase() + source.slice( 1 );
};

const isSafeTextValue = (
	definition: PropertyDefinition,
	value: string
): boolean => {
	if ( definition.type === 'color' ) {
		return /^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/.test(
			value
		);
	}
	if ( definition.type === 'dimension' ) {
		return /^(?:0|(?:\d+|\d*\.\d+)(?:px|rem|em|%|vw|vh|vmin|vmax|ch|ex))$/.test(
			value
		);
	}
	return true;
};

const PropertyTextControl = ( {
	definition,
	value,
	help,
	onChange,
	onReset,
}: {
	definition: PropertyDefinition;
	value: StyleValue | undefined;
	help: string;
	onChange: ( value: StyleValue ) => void;
	onReset: () => void;
} ) => {
	const [ draft, setDraft ] = useState(
		value === undefined ? '' : String( value )
	);
	const [ valid, setValid ] = useState( true );

	useEffect( () => {
		setDraft( value === undefined ? '' : String( value ) );
		setValid( true );
	}, [ value ] );

	const commit = () => {
		const next = draft.trim();
		if ( next === '' ) {
			onReset();
			setValid( true );
			return;
		}
		if ( isSafeTextValue( definition, next ) ) {
			onChange( next );
			setValid( true );
			return;
		}
		setValid( false );
	};
	let helpText = help;
	if ( ! valid ) {
		helpText =
			definition.type === 'color'
				? 'Enter a safe hex color, for example #1d4ed8.'
				: 'Enter a non-negative CSS dimension, for example 8px or 1rem.';
	}

	return (
		<TextControl
			label={ definition.label }
			value={ draft }
			placeholder="Inherit"
			help={ helpText }
			onChange={ setDraft }
			onBlur={ commit }
			onKeyDown={ ( event: KeyboardEvent< HTMLInputElement > ) => {
				if ( event.key === 'Enter' ) {
					event.currentTarget.blur();
				}
			} }
		/>
	);
};

export function TableScopeControls( {
	scopeLabel,
	scope,
	effective,
	definitions,
	presets,
	onChange,
	onResetAll,
}: TableScopeControlsProps ) {
	const preset = effective.preset
		? presets.find( ( item ) => item.id === effective.preset )
		: null;

	return (
		<>
			<Notice status="info" isDismissible={ false }>
				Effective preset: { preset?.label ?? 'Theme Default' }.
				Inherited from: { sourceLabel( effective.presetSource ) }.
			</Notice>
			<SelectControl
				label="Table preset"
				value={ scope.preset ?? '' }
				options={ [
					{
						label:
							scopeLabel === 'Post'
								? 'Inherit Global'
								: 'Inherit Post/Global',
						value: '',
					},
					...presets
						.filter( ( item ) => item.id !== THEME_DEFAULT_PRESET )
						.map( ( item ) => ( {
							label: item.label,
							value: item.id,
						} ) ),
				] }
				onChange={ ( value ) =>
					onChange(
						setScopePreset( scope, value === '' ? null : value )
					)
				}
			/>

			{ Object.entries( TABLE_PROPERTY_GROUPS ).map(
				( [ group, propertyIds ] ) => (
					<PanelBody
						title={ group }
						initialOpen={ group === 'Container' }
						key={ group }
					>
						{ propertyIds.map( ( propertyId ) => {
							const definition = definitions[ propertyId ];
							const resolved = effective.properties[ propertyId ];
							if ( ! definition || ! resolved ) {
								return null;
							}
							const value = scope.properties[ propertyId ];
							const help =
								value === undefined
									? `${
											resolved.value === null
												? 'Theme-owned'
												: String( resolved.value )
										} — inherited from ${ sourceLabel( resolved.source ) }`
									: `${ String( value ) } — ${ scopeLabel } override`;

							if (
								definition.type === 'enum' ||
								definition.type === 'boolean'
							) {
								const options =
									definition.type === 'boolean'
										? [
												{
													label: `Inherit (${ help })`,
													value: '',
												},
												{ label: 'On', value: 'true' },
												{
													label: 'Off',
													value: 'false',
												},
											]
										: [
												{
													label: `Inherit (${ help })`,
													value: '',
												},
												...definition.allowedValues.map(
													( option ) => ( {
														label: String( option ),
														value: String( option ),
													} )
												),
											];
								return (
									<SelectControl
										key={ propertyId }
										label={ definition.label }
										help={ help }
										value={
											value === undefined
												? ''
												: String( value )
										}
										options={ options }
										onChange={ ( next ) => {
											if ( next === '' ) {
												onChange(
													resetScopeProperty(
														scope,
														propertyId
													)
												);
											} else {
												onChange(
													setScopeProperty(
														scope,
														propertyId,
														definition.type ===
															'boolean'
															? next === 'true'
															: next
													)
												);
											}
										} }
									/>
								);
							}

							return (
								<PropertyTextControl
									key={ propertyId }
									definition={ definition }
									value={ value }
									help={ help }
									onChange={ ( next ) =>
										onChange(
											setScopeProperty(
												scope,
												propertyId,
												next
											)
										)
									}
									onReset={ () =>
										onChange(
											resetScopeProperty(
												scope,
												propertyId
											)
										)
									}
								/>
							);
						} ) }
					</PanelBody>
				)
			) }
			<Button variant="secondary" isDestructive onClick={ onResetAll }>
				{ scopeLabel === 'Post'
					? 'Reset to Global'
					: 'Reset all block overrides' }
			</Button>
		</>
	);
}
