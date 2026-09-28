import { InspectorControls } from '@wordpress/block-editor';
import { Notice, PanelBody, Spinner } from '@wordpress/components';
import { createHigherOrderComponent } from '@wordpress/compose';
import { useDispatch, useSelect } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { addFilter } from '@wordpress/hooks';
import { registerPlugin } from '@wordpress/plugins';
import type { ComponentType, CSSProperties } from 'react';
import { TableScopeControls } from './TableScopeControls';
import { useTableData } from './table-data';
import { resolveStyle } from '../style-engine/resolver';
import {
	createScopeStyle,
	getModuleStyle,
	isScopeStyleEmpty,
	setModuleStyle,
} from '../style-engine/scope-style';
import {
	compileTablePresentation,
	getNativeTableOverrides,
	TABLE_MODULE,
} from '../table/table-style';
import type { ScopeStyle, StyleDocument } from '../types/style-engine';

const META_KEY = '_gutenstyle_styles';

interface BlockSettings {
	attributes?: Record< string, unknown >;
	[ key: string ]: unknown;
}

interface BlockEditProps {
	name: string;
	attributes: Record< string, unknown >;
	setAttributes: ( attributes: Record< string, unknown > ) => void;
}

interface BlockListBlockProps {
	name: string;
	attributes: Record< string, unknown >;
	wrapperProps?: {
		className?: string;
		style?: CSSProperties;
		[ key: string ]: unknown;
	};
	[ key: string ]: unknown;
}

interface EditorSelector {
	getEditedPostAttribute: ( attribute: string ) => unknown;
}

interface EditorDispatcher {
	editPost: ( values: Record< string, unknown > ) => void;
}

const emptyDocument = (): StyleDocument => ( { version: 1, blocks: {} } );

const asStyleDocument = ( value: unknown ): StyleDocument => {
	if ( ! value || typeof value !== 'object' ) {
		return emptyDocument();
	}
	const document = value as Partial< StyleDocument >;
	return document.version === 1 && document.blocks
		? ( document as StyleDocument )
		: emptyDocument();
};

const asScopeStyle = ( value: unknown ): ScopeStyle => {
	if ( ! value || typeof value !== 'object' ) {
		return createScopeStyle();
	}
	const scope = value as Partial< ScopeStyle >;
	return scope.version === 1 && scope.properties
		? ( scope as ScopeStyle )
		: createScopeStyle();
};

const usePostStyles = () => {
	const meta = useSelect(
		( select ) =>
			(
				select( 'core/editor' ) as unknown as EditorSelector
			 ).getEditedPostAttribute( 'meta' ),
		[]
	) as Record< string, unknown > | undefined;
	const dispatcher = useDispatch(
		'core/editor'
	) as unknown as EditorDispatcher;
	const document = asStyleDocument( meta?.[ META_KEY ] );

	return {
		document,
		setDocument: ( next: StyleDocument ) =>
			dispatcher.editPost( {
				meta: { ...meta, [ META_KEY ]: next },
			} ),
	};
};

function TableDocumentPanel() {
	const { data, error } = useTableData();
	const { document, setDocument } = usePostStyles();

	if ( error ) {
		return (
			<PluginDocumentSettingPanel
				name="gutenstyle-table"
				title="GutenStyle Table"
			>
				<Notice status="error" isDismissible={ false }>
					{ error }
				</Notice>
			</PluginDocumentSettingPanel>
		);
	}
	if ( ! data ) {
		return (
			<PluginDocumentSettingPanel
				name="gutenstyle-table"
				title="GutenStyle Table"
			>
				<Spinner />
			</PluginDocumentSettingPanel>
		);
	}

	const scope = getModuleStyle( document, TABLE_MODULE );
	const global = getModuleStyle( data.global, TABLE_MODULE );
	const effective = resolveStyle( TABLE_MODULE, data.schema, {
		global,
		post: scope,
	} );
	const presets = Object.values( data.schema.presets ).filter(
		( preset ) => preset.module === TABLE_MODULE
	);

	return (
		<PluginDocumentSettingPanel
			name="gutenstyle-table"
			title="GutenStyle Table"
		>
			<TableScopeControls
				scopeLabel="Post"
				scope={ scope }
				effective={ effective }
				definitions={ data.schema.properties }
				presets={ presets }
				onChange={ ( next ) =>
					setDocument(
						setModuleStyle( document, TABLE_MODULE, next )
					)
				}
				onResetAll={ () =>
					setDocument(
						setModuleStyle(
							document,
							TABLE_MODULE,
							createScopeStyle()
						)
					)
				}
			/>
		</PluginDocumentSettingPanel>
	);
}

function TableInspector( props: BlockEditProps ) {
	const { data, error } = useTableData();
	const { document } = usePostStyles();

	if ( error ) {
		return (
			<InspectorControls>
				<PanelBody title="GutenStyle Table">
					<Notice status="error" isDismissible={ false }>
						{ error }
					</Notice>
				</PanelBody>
			</InspectorControls>
		);
	}
	if ( ! data ) {
		return (
			<InspectorControls>
				<PanelBody title="GutenStyle Table">
					<Spinner />
				</PanelBody>
			</InspectorControls>
		);
	}

	const scope = asScopeStyle( props.attributes.gutenstyle );
	const effective = resolveStyle( TABLE_MODULE, data.schema, {
		global: getModuleStyle( data.global, TABLE_MODULE ),
		post: getModuleStyle( document, TABLE_MODULE ),
		block: scope,
	} );
	const presets = Object.values( data.schema.presets ).filter(
		( preset ) => preset.module === TABLE_MODULE
	);
	const setScope = ( next: ScopeStyle ) =>
		props.setAttributes( {
			gutenstyle: isScopeStyleEmpty( next ) ? undefined : next,
		} );

	return (
		<InspectorControls>
			<PanelBody title="GutenStyle Table" initialOpen>
				<TableScopeControls
					scopeLabel="Block"
					scope={ scope }
					effective={ effective }
					definitions={ data.schema.properties }
					presets={ presets }
					onChange={ setScope }
					onResetAll={ () =>
						props.setAttributes( { gutenstyle: undefined } )
					}
				/>
			</PanelBody>
		</InspectorControls>
	);
}

function StyledTableBlock( {
	BlockListBlock,
	props,
}: {
	BlockListBlock: ComponentType< BlockListBlockProps >;
	props: BlockListBlockProps;
} ) {
	const { data } = useTableData();
	const { document } = usePostStyles();
	if ( ! data ) {
		return <BlockListBlock { ...props } />;
	}

	const effective = resolveStyle( TABLE_MODULE, data.schema, {
		global: getModuleStyle( data.global, TABLE_MODULE ),
		post: getModuleStyle( document, TABLE_MODULE ),
		block: asScopeStyle( props.attributes.gutenstyle ),
	} );
	const presentation = compileTablePresentation(
		effective,
		data.schema.adapters[ TABLE_MODULE ],
		getNativeTableOverrides( props.attributes )
	);
	const wrapperProps = props.wrapperProps ?? {};

	return (
		<BlockListBlock
			{ ...props }
			wrapperProps={ {
				...wrapperProps,
				className: [
					wrapperProps.className,
					'gutenstyle-editor-table',
					presentation.className,
				]
					.filter( Boolean )
					.join( ' ' ),
				style: {
					...wrapperProps.style,
					...presentation.wrapperStyle,
				},
			} }
		/>
	);
}

addFilter(
	'blocks.registerBlockType',
	'gutenstyle/table-attribute',
	( settings: BlockSettings, name: string ): BlockSettings => {
		if ( name !== TABLE_MODULE ) {
			return settings;
		}
		return {
			...settings,
			attributes: {
				...settings.attributes,
				gutenstyle: { type: 'object' },
			},
		};
	}
);

addFilter(
	'editor.BlockEdit',
	'gutenstyle/table-inspector',
	createHigherOrderComponent(
		( BlockEdit: ComponentType< BlockEditProps > ) =>
			function GutenStyleTableBlockEdit( props: BlockEditProps ) {
				return (
					<>
						<BlockEdit { ...props } />
						{ props.name === TABLE_MODULE && (
							<TableInspector { ...props } />
						) }
					</>
				);
			},
		'withGutenStyleTableInspector'
	)
);

addFilter(
	'editor.BlockListBlock',
	'gutenstyle/table-preview',
	createHigherOrderComponent(
		( BlockListBlock: ComponentType< BlockListBlockProps > ) =>
			function GutenStyleTableBlockList( props: BlockListBlockProps ) {
				return props.name === TABLE_MODULE ? (
					<StyledTableBlock
						BlockListBlock={ BlockListBlock }
						props={ props }
					/>
				) : (
					<BlockListBlock { ...props } />
				);
			},
		'withGutenStyleTablePreview'
	)
);

registerPlugin( 'gutenstyle-table-document', {
	render: TableDocumentPanel,
} );
