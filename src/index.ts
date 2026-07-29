import { addAction } from '@wordpress/hooks';
import { registerModule } from '@divi/module-library';

import { searchResultTypeModule } from './components/search-result-type';
import { searchResultsModule } from './components/search-results';

addAction('divi.moduleLibrary.registerModuleLibraryStore.after', 'codecorn/search-results', () => {
  const { metadata: resultTypeMetadata, ...resultTypeDefinition } = searchResultTypeModule;
  const { metadata: searchResultsMetadata, ...searchResultsDefinition } = searchResultsModule;

  registerModule(resultTypeMetadata, resultTypeDefinition);
  registerModule(searchResultsMetadata, searchResultsDefinition);
});
