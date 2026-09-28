import { createRoot } from '@wordpress/element';
import { ConfigProvider, Card, Typography, Tag } from 'antd';
import '../styles/admin.css';

const { Title, Paragraph } = Typography;

function App() {
	return (
		<ConfigProvider prefixCls="gutenstyle" iconPrefixCls="gutenstyle-icon">
			<div className="gs:min-h-screen gs:bg-slate-50 gs:p-6">
				<div className="gs:mx-auto gs:max-w-6xl">
					<div className="gs:mb-6">
						<Title level={ 1 }>GutenStyle</Title>
						<Paragraph>
							Style native Gutenberg blocks globally, per post, or
							individually.
						</Paragraph>
					</div>

					<div className="gs:grid gs:grid-cols-1 gs:gap-4 md:gs:grid-cols-3">
						<Card title="Global Styles">
							<Paragraph>
								Site-wide defaults for supported native blocks.
							</Paragraph>
							<Tag>Foundation</Tag>
						</Card>

						<Card title="Style Profiles">
							<Paragraph>
								Reusable content-wide style systems.
							</Paragraph>
							<Tag>Planned</Tag>
						</Card>

						<Card title="Block Styling">
							<Paragraph>
								Override individual blocks inside Gutenberg.
							</Paragraph>
							<Tag>Foundation</Tag>
						</Card>
					</div>
				</div>
			</div>
		</ConfigProvider>
	);
}

const container = document.getElementById( 'gutenstyle-admin-app' );

if ( container ) {
	createRoot( container ).render( <App /> );
}
