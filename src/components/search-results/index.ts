import { type Metadata, type ModuleLibrary } from '@divi/types';

import metadata from './module.json';
import { SearchResultsEdit } from './edit';
import { SearchResultsAttrs } from './types';

import './module.scss';

export const searchResultsModule: ModuleLibrary.Module.RegisterDefinition<SearchResultsAttrs> = {
  metadata: metadata as Metadata.Values<SearchResultsAttrs>,
  childrenName: ['codecorn/search-result-type'],
  template: [
    ['codecorn/search-result-type', { rule: { innerContent: { desktop: { value: { postType: 'post' } } } } }],
    ['codecorn/search-result-type', { rule: { innerContent: { desktop: { value: { postType: 'page' } } } } }],
  ],
  renderers: {
    edit: SearchResultsEdit,
  },
};
