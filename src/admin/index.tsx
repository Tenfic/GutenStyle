import apiFetch from '@wordpress/api-fetch';
import { createRoot, useEffect, useMemo, useState } from '@wordpress/element';
import {
	Alert,
	Button,
	Card,
	ConfigProvider,
	Divider,
	Flex,
	Spin,
	Switch,
	Tag,
	Typography,
	message,
} from 'antd';
import { PresetBrowser } from './components/PresetBrowser';
import { TablePreview } from './components/TablePreview';
import { TablePropertyControls } from './components/TablePropertyControls';
import { resolveStyle } from '../style-engine/resolver';
import {
	createScopeStyle,
	getModuleStyle,
	resetScopeProperty,
	setModuleStyle,
	setScopePreset,
	setScopeProperty,
} from '../style-engine/scope-style';
import {
	compileTablePresentation,
	TABLE_MODULE,
	THEME_DEFAULT_PRESET,
} from '../table/table-style';
import type {
	StyleDocument,
	StyleSchema,
	StyleValue,
} from '../types/style-engine';
import '../styles/admin.css';

const { Title, Paragraph, Text } = Typography;

const emptyDocument = (): StyleDocument => ( { version: 1, blocks: {} } );

function App() {
	const [ schema, setSchema ] = useState< StyleSchema | null >( null );
	const [ saved, setSaved ] = useState< StyleDocument >( emptyDocument );
	const [ draft, setDraft ] = useState< StyleDocument >( emptyDocument );
	const [ loading, setLoading ] = useState( true );
	const [ saving, setSaving ] = useState( false );
	const [ error, setError ] = useState< string | null >( null );
	const [ messageApi, contextHolder ] = message.useMessage();

	useEffect( () => {
		Promise.all( [
			apiFetch< StyleSchema >( { path: '/gutenstyle/v1/schema' } ),
			apiFetch< StyleDocument >( {
				path: '/gutenstyle/v1/styles/global',
			} ),
		] )
			.then( ( [ nextSchema, nextDocument ] ) => {
				setSchema( nextSchema );
				setSaved( nextDocument );
				setDraft( nextDocument );
			} )
			.catch( ( reason: unknown ) => {
				setError(
					reason instanceof Error
						? reason.message
						: 'GutenStyle settings could not be loaded.'
				);
			} )
			.finally( () => setLoading( false ) );
	}, [] );

	const scope = getModuleStyle( draft, TABLE_MODULE );
	const enabled = Boolean( draft.blocks[ TABLE_MODULE ] );
	const dirty = JSON.stringify( saved ) !== JSON.stringify( draft );
	const tablePresets = useMemo(
		() =>
			schema
				? Object.values( schema.presets ).filter(
						( preset ) => preset.module === TABLE_MODULE
					)
				: [],
		[ schema ]
	);
	const effective = useMemo(
		() =>
			schema
				? resolveStyle( TABLE_MODULE, schema, {
						global: enabled ? scope : createScopeStyle(),
					} )
				: null,
		[ enabled, schema, scope ]
	);
	const presentation = useMemo(
		() =>
			schema && effective
				? compileTablePresentation(
						effective,
						schema.adapters[ TABLE_MODULE ]
					)
				: null,
		[ effective, schema ]
	);

	const updateScope = ( nextScope: typeof scope ) =>
		setDraft( ( current ) =>
			setModuleStyle( current, TABLE_MODULE, nextScope )
		);

	const save = async () => {
		setSaving( true );
		try {
			const next = await apiFetch< StyleDocument >( {
				path: '/gutenstyle/v1/styles/global',
				method: 'PUT',
				data: draft,
			} );
			setSaved( next );
			setDraft( next );
			messageApi.success( 'Global Table styles saved.' );
		} catch ( reason ) {
			messageApi.error(
				reason instanceof Error
					? reason.message
					: 'Styles could not be saved.'
			);
		} finally {
			setSaving( false );
		}
	};

	return (
		<ConfigProvider prefixCls="gutenstyle" iconPrefixCls="gutenstyle-icon">
			{ contextHolder }
			<div className="gs:min-h-screen gs:bg-slate-50 gs:p-6">
				<div className="gs:mx-auto gs:max-w-7xl">
					<div className="gs:mb-6">
						<Title level={ 1 }>Global Styles</Title>
						<Paragraph>
							Define site-wide defaults for native Gutenberg
							blocks.
						</Paragraph>
					</div>

					{ loading && <Spin size="large" /> }
					{ error && (
						<Alert type="error" showIcon message={ error } />
					) }
					{ schema && effective && presentation && (
						<div className="gs:grid gs:grid-cols-1 gs:gap-6 xl:gs:grid-cols-2">
							<div className="gs:flex gs:flex-col gs:gap-6">
								<Card>
									<Flex
										justify="space-between"
										align="center"
										gap={ 16 }
									>
										<div>
											<Title level={ 2 }>Table</Title>
											<Text type="secondary">
												Enhance native core/table blocks
												without replacing them.
											</Text>
										</div>
										<Switch
											checked={ enabled }
											checkedChildren="Enabled"
											unCheckedChildren="Disabled"
											onChange={ ( checked ) =>
												updateScope(
													checked
														? setScopePreset(
																createScopeStyle(),
																THEME_DEFAULT_PRESET
															)
														: createScopeStyle()
												)
											}
										/>
									</Flex>
								</Card>

								<Card title="Preset">
									<PresetBrowser
										presets={ tablePresets }
										selected={
											enabled ? scope.preset : null
										}
										onSelect={ ( presetId ) =>
											updateScope(
												setScopePreset(
													scope,
													presetId
												)
											)
										}
									/>
								</Card>

								<Card title="Property overrides">
									<TablePropertyControls
										definitions={ schema.properties }
										scope={ scope }
										effective={ effective }
										onChange={ (
											propertyId: string,
											value: StyleValue
										) =>
											updateScope(
												setScopeProperty(
													scope,
													propertyId,
													value
												)
											)
										}
										onReset={ ( propertyId ) =>
											updateScope(
												resetScopeProperty(
													scope,
													propertyId
												)
											)
										}
									/>
								</Card>
							</div>

							<div className="gs:flex gs:flex-col gs:gap-6">
								<Card
									title="Live preview"
									className="gutenstyle-preview-card"
								>
									<TablePreview
										presentation={ presentation }
									/>
								</Card>
								<Card>
									<Flex
										justify="space-between"
										align="center"
										gap={ 16 }
										wrap
									>
										<div>
											{ dirty ? (
												<Tag color="gold">
													Unsaved changes
												</Tag>
											) : (
												<Tag color="green">Saved</Tag>
											) }
										</div>
										<Flex gap={ 8 }>
											<Button
												danger
												disabled={ ! enabled }
												onClick={ () =>
													updateScope(
														createScopeStyle()
													)
												}
											>
												Reset Table
											</Button>
											<Button
												onClick={ () =>
													setDraft( saved )
												}
												disabled={ ! dirty }
											>
												Discard
											</Button>
											<Button
												type="primary"
												loading={ saving }
												disabled={ ! dirty }
												onClick={ save }
											>
												Save styles
											</Button>
										</Flex>
									</Flex>
									<Divider />
									<Text type="secondary">
										Reset removes GutenStyle values so
										Theme/Core can inherit again.
									</Text>
								</Card>
							</div>
						</div>
					) }
				</div>
			</div>
		</ConfigProvider>
	);
}

const container = document.getElementById( 'gutenstyle-admin-app' );

if ( container ) {
	createRoot( container ).render( <App /> );
}
