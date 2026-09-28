import { Card, Typography } from 'antd';
import type { PresetDefinition } from '../../types/style-engine';

const { Text } = Typography;

interface PresetBrowserProps {
	presets: PresetDefinition[];
	selected: string | null;
	onSelect: ( presetId: string ) => void;
}

const swatchStyle = ( preset: PresetDefinition ) => ( {
	backgroundColor:
		typeof preset.properties.headerBackgroundColor === 'string'
			? preset.properties.headerBackgroundColor
			: '#f8fafc',
	borderColor:
		typeof preset.properties.tableBorderColor === 'string'
			? preset.properties.tableBorderColor
			: '#d8dee8',
} );

/**
 * Reusable visual preset chooser for future native-block modules.
 * @param root0
 * @param root0.presets
 * @param root0.selected
 * @param root0.onSelect
 */
export function PresetBrowser( {
	presets,
	selected,
	onSelect,
}: PresetBrowserProps ) {
	return (
		<div
			className="gs:grid gs:grid-cols-2 gs:gap-3 lg:gs:grid-cols-3"
			role="radiogroup"
			aria-label="Table preset"
		>
			{ presets.map( ( preset ) => (
				<Card
					key={ preset.id }
					size="small"
					hoverable
					className={
						selected === preset.id
							? 'gutenstyle-preset-card is-selected'
							: 'gutenstyle-preset-card'
					}
					onClick={ () => onSelect( preset.id ) }
					role="radio"
					aria-checked={ selected === preset.id }
					tabIndex={ 0 }
					onKeyDown={ ( event ) => {
						if ( event.key === 'Enter' || event.key === ' ' ) {
							event.preventDefault();
							onSelect( preset.id );
						}
					} }
				>
					<div
						className="gutenstyle-preset-swatch"
						style={ swatchStyle( preset ) }
					>
						<span />
						<span />
						<span />
					</div>
					<Text strong>{ preset.label }</Text>
				</Card>
			) ) }
		</div>
	);
}
