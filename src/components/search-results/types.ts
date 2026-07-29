import { ModuleEditProps } from '@divi/module-library';
import { FormatBreakpointStateAttr, InternalAttrs, type Element } from '@divi/types';

export interface SearchResultsQueryValue {
  source?: string;
  searchTerm?: string;
  postsPerPage?: string;
}

export interface SearchResultsDisplayValue {
  showSummary?: string;
  showSearchForm?: string;
  columns?: string;
}

export interface SearchResultsEmptyValue {
  title?: string;
  body?: string;
}

export interface SearchResultsAttrs extends InternalAttrs {
  module?: {
    meta?: Element.Meta.Attributes;
    advanced?: Record<string, unknown>;
    decoration?: Record<string, unknown>;
  };
  query?: { innerContent?: FormatBreakpointStateAttr<SearchResultsQueryValue> };
  display?: { innerContent?: FormatBreakpointStateAttr<SearchResultsDisplayValue> };
  emptyState?: { innerContent?: FormatBreakpointStateAttr<SearchResultsEmptyValue> };
}

export type SearchResultsEditProps = ModuleEditProps<SearchResultsAttrs>;
