import { registerModule } from '@divi/module-library';
import { addAction } from '@wordpress/hooks';

import {
  prepareSearchResultTypeMetadata,
  prepareSearchResultsMetadata,
} from './builder-data';
import { searchResultTypeModule } from './components/search-result-type';
import { searchResultsModule } from './components/search-results';

addAction('divi.moduleLibrary.registerModuleLibraryStore.after', 'codecorn/search-results', () => {
  const { metadata: rawResultTypeMetadata, ...resultTypeDefinition } = searchResultTypeModule;
  const { metadata: rawSearchResultsMetadata, ...searchResultsDefinition } = searchResultsModule;

  const resultTypeMetadata = prepareSearchResultTypeMetadata(rawResultTypeMetadata);
  const searchResultsMetadata = prepareSearchResultsMetadata(rawSearchResultsMetadata);

  registerModule(resultTypeMetadata, resultTypeDefinition);
  registerModule(searchResultsMetadata, searchResultsDefinition);
});
