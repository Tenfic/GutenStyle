import {
	Button,
	ColorPicker,
	Input,
	Select,
	Switch,
	Tag,
	Typography,
} from 'antd';
import type {
	EffectiveStyle,
	PropertyDefinition,
	ScopeStyle,
	StyleValue,
} from '../../types/style-engine';
import { TABLE_PROPERTY_GROUPS } from '../../table/table-style';

const { Text } = Typography;

interface TablePropertyControlsProps {
	definitions: Record< string, PropertyDefinition >;
	scope: ScopeStyle;
	effective: EffectiveStyle;
	onChange: ( propertyId: string, value: StyleValue ) => void;
	onReset: ( propertyId: string ) => void;
}

const formatSource = ( source: string ): string =>
	source === 'theme'
		? 'Theme/Core'
		: source[ 0 ].toUpperCase() + source.slice( 1 );

const Control = ( {
	definition,
	value,
	effectiveValue,
	onChange,
}: {
	definition: PropertyDefinition;
	value: StyleValue | undefined;
	effectiveValue: StyleValue | null;
	onChange: ( value: StyleValue ) => void;
} ) => {
	if ( definition.type === 'boolean' ) {
		return (
			<Switch
				checked={
					value === undefined
						? effectiveValue === true
						: value === true
				}
				onChange={ onChange }
			/>
		);
	}

	if ( definition.type === 'enum' ) {
		return (
			<Select
				value={ value === undefined ? undefined : String( value ) }
				placeholder={
					effectiveValue === null
						? 'Inherit'
						: String( effectiveValue )
				}
				options={ definition.allowedValues.map( ( option ) => ( {
					value: String( option ),
					label: String( option )
						.replace( /-/g, ' ' )
						.replace( /^./, ( letter: string ) =>
							letter.toUpperCase()
						),
				} ) ) }
				onChange={ onChange }
				className="gs:w-full"
			/>
		);
	}
	if ( definition.type === 'color' ) {
		return (
			<ColorPicker
				value={ String( value ?? effectiveValue ?? '#ffffff' ) }
				showText
				onChangeComplete={ ( color ) =>
					onChange( color.toHexString() )
				}
			/>
		);
	}

	return (
		<Input
			value={ value === undefined ? '' : String( value ) }
			placeholder={
				effectiveValue === null ? 'Inherit' : String( effectiveValue )
			}
			onChange={ ( event ) => onChange( event.target.value ) }
			aria-label={ definition.label }
		/>
	);
};

export function TablePropertyControls( {
	definitions,
	scope,
	effective,
	onChange,
	onReset,
}: TablePropertyControlsProps ) {
	return (
		<div className="gs:flex gs:flex-col gs:gap-5">
			{ Object.entries( TABLE_PROPERTY_GROUPS ).map(
				( [ group, propertyIds ] ) => (
					<section
						key={ group }
						aria-labelledby={ `table-${ group }` }
					>
						<h3 id={ `table-${ group }` }>{ group }</h3>
						<div className="gutenstyle-control-list">
							{ propertyIds.map( ( propertyId ) => {
								const definition = definitions[ propertyId ];
								const resolved =
									effective.properties[ propertyId ];
								if ( ! definition || ! resolved ) {
									return null;
								}
								const value = scope.properties[ propertyId ];

								return (
									<div
										className="gutenstyle-control-row"
										key={ propertyId }
									>
										<div>
											<Text strong>
												{ definition.label }
											</Text>
											<div>
												<Tag bordered={ false }>
													{ value === undefined
														? `Inherited: ${ formatSource( resolved.source ) }`
														: 'Global override' }
												</Tag>
											</div>
										</div>
										<div className="gutenstyle-control-input">
											<Control
												definition={ definition }
												value={ value }
												effectiveValue={
													resolved.value
												}
												onChange={ ( next ) =>
													onChange( propertyId, next )
												}
											/>
											<Button
												type="link"
												size="small"
												disabled={ value === undefined }
												onClick={ () =>
													onReset( propertyId )
												}
											>
												Reset
											</Button>
										</div>
									</div>
								);
							} ) }
						</div>
					</section>
				)
			) }
		</div>
	);
}
