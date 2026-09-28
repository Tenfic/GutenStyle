declare module '@wordpress/block-editor' {
	import type { ComponentType, ReactNode } from 'react';

	export const InspectorControls: ComponentType< {
		children?: ReactNode;
		group?: string;
	} >;
}
