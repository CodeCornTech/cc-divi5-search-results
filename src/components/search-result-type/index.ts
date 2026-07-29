import { type Metadata, type ModuleLibrary } from '@divi/types';

import metadata from './module.json';
import { SearchResultTypeEdit } from './edit';
import { SearchResultTypeAttrs } from './types';

import './module.scss';

export const searchResultTypeModule: ModuleLibrary.Module.RegisterDefinition<SearchResultTypeAttrs> = {
  metadata: metadata as Metadata.Values<SearchResultTypeAttrs>,
  renderers: {
    edit: SearchResultTypeEdit,
  },
};
