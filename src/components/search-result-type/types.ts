import { ModuleEditProps } from '@divi/module-library';
import { FormatBreakpointStateAttr, InternalAttrs, type Element } from '@divi/types';

export interface ResultTypeRuleValue {
  postType?: string;
  badgeLabel?: string;
  accentColor?: string;
  priority?: string;
}

export interface SearchResultTypeAttrs extends InternalAttrs {
  module?: {
    meta?: Element.Meta.Attributes;
    advanced?: Record<string, unknown>;
    decoration?: Record<string, unknown>;
  };
  rule?: { innerContent?: FormatBreakpointStateAttr<ResultTypeRuleValue> };
}

export type SearchResultTypeEditProps = ModuleEditProps<SearchResultTypeAttrs>;
