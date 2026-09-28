export type StyleValue = boolean | number | string;

export type PropertyType =
	'boolean' | 'enum' | 'color' | 'number' | 'dimension' | 'string';

export interface PropertyDefinition {
	id: string;
	label: string;
	type: PropertyType;
	defaultBehavior: 'inherit' | 'initial';
	allowedValues: StyleValue[];
	min: number | null;
	max: number | null;
	responsive: boolean;
	cssVariables: Record< string, string >;
	modules: string[];
}

export type StyleProperties = Record< string, StyleValue >;

export interface PresetDefinition {
	id: string;
	label: string;
	module: string;
	properties: StyleProperties;
}

export interface ScopeStyle {
	version: 1;
	preset: string | null;
	properties: StyleProperties;
}

export type StyleOrigin = 'theme' | 'global' | 'post' | 'block';
export type ResolutionPath = 'none' | 'preset' | 'property';

export interface ResolvedProperty {
	value: StyleValue | null;
	source: StyleOrigin;
	via: ResolutionPath;
	preset: string | null;
}

export interface EffectiveStyle {
	module: string;
	preset: string | null;
	presetSource: StyleOrigin;
	properties: Record< string, ResolvedProperty >;
}

export interface GutenStyleBlockAttributes {
	gutenstyle?: ScopeStyle;
}

export interface StyleDocument {
	version: 1;
	blocks: Record< string, ScopeStyle >;
}
