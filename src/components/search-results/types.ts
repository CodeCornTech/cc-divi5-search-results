import { ModuleEditProps } from '@divi/module-library';
import {
  FormatBreakpointStateAttr,
  InternalAttrs,
  type Element,
} from '@divi/types';

export interface SearchResultsQueryValue {
  source?: string;
  searchTerm?: string;
  postsPerPage?: string;
}

export interface SearchResultsDisplayValue {
  showSummary?: string;
  showSearchForm?: string;
  preset?: string;
  columns?: string;
  ajaxPagination?: string;
}

export interface SearchResultsEmptyValue {
  title?: string;
  body?: string;
}

export interface SearchResultsAttrs extends InternalAttrs {
  module?: {
    meta?: Element.Meta.Attributes;
    advanced?: {
      link?: Element.Advanced.Link.Attributes;
      htmlAttributes?: Element.Advanced.IdClasses.Attributes;
      text?: Element.Advanced.Text.Attributes;
    };
    decoration?: Element.Decoration.PickedAttributes<
      'animation' |
      'background' |
      'border' |
      'boxShadow' |
      'disabledOn' |
      'filters' |
      'overflow' |
      'position' |
      'scroll' |
      'sizing' |
      'spacing' |
      'sticky' |
      'transform' |
      'transition' |
      'zIndex'
    >;
  };
  query?: { innerContent?: FormatBreakpointStateAttr<SearchResultsQueryValue> };
  display?: { innerContent?: FormatBreakpointStateAttr<SearchResultsDisplayValue> };
  emptyState?: { innerContent?: FormatBreakpointStateAttr<SearchResultsEmptyValue> };
}

export type SearchResultsEditProps = ModuleEditProps<SearchResultsAttrs>;
