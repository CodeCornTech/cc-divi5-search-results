import { ModuleEditProps } from '@divi/module-library';
import {
  FormatBreakpointStateAttr,
  InternalAttrs,
  type Element,
} from '@divi/types';

import { SearchResultsAttrs } from '../search-results/types';

export interface ResultTypeRuleValue {
  postType?: string;
  singularLabel?: string;
  pluralLabel?: string;
  badgeLabel?: string;
  accentColor?: string;
  priority?: string;
  showImage?: string;
  showExcerpt?: string;
  showDate?: string;
  excerptLength?: string;
  ctaLabel?: string;
}

export interface SearchResultTypeAttrs extends InternalAttrs {
  module?: {
    meta?: Element.Meta.Attributes;
    advanced?: {
      htmlAttributes?: Element.Advanced.IdClasses.Attributes;
    };
    decoration?: Element.Decoration.PickedAttributes<'disabledOn'>;
  };
  rule?: { innerContent?: FormatBreakpointStateAttr<ResultTypeRuleValue> };
}

export type SearchResultTypeEditProps = ModuleEditProps<
  SearchResultTypeAttrs,
  SearchResultsAttrs
>;
