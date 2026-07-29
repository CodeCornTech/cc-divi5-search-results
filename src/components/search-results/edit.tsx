import React, { ReactElement } from 'react';
import { ChildModulesContainer, ModuleContainer } from '@divi/module';

import { moduleClassnames } from './module-classnames';
import { ModuleStyles } from './styles';
import { SearchResultsEditProps } from './types';

export const SearchResultsEdit = (props: SearchResultsEditProps): ReactElement => {
  const { attrs, childrenIds, elements, id, name } = props;
  const query = attrs.query?.innerContent?.desktop?.value;

  return (
    <ModuleContainer
      attrs={attrs}
      elements={elements}
      id={id}
      name={name}
      tag="section"
      classnamesFunction={moduleClassnames}
      stylesComponent={ModuleStyles}
    >
      {elements.styleComponents({ attrName: 'module' })}
      <div className="cc-d5sr-vb-preview">
        <strong>Search Results</strong>
        <span>
          {query?.source === 'isolated'
            ? `Isolated query: ${query.searchTerm || 'no search term'}`
            : 'Current WordPress search query'}
        </span>
        <small>Result Type Rule children configure the frontend cards.</small>
      </div>
      <ChildModulesContainer ids={childrenIds} />
    </ModuleContainer>
  );
};
