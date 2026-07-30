import React, { ReactElement } from 'react';
import { ChildModulesContainer, ModuleContainer } from '@divi/module';

import { moduleClassnames } from './module-classnames';
import { ModuleStyles } from './styles';
import { SearchResultsEditProps } from './types';

const presetLabels: Record<string, string> = {
  grid: 'Editorial grid',
  'compact-list': 'Compact vertical list',
  'classic-card': 'Classic image card',
};

export const SearchResultsEdit = (props: SearchResultsEditProps): ReactElement => {
  const {
    attrs,
    childrenIds = [],
    elements,
    id,
    name,
  } = props;

  if (! elements) {
    return <></>;
  }

  const query = attrs.query?.innerContent?.desktop?.value;
  const displayDesktop = attrs.display?.innerContent?.desktop?.value;
  const displayTablet = attrs.display?.innerContent?.tablet?.value;
  const displayPhone = attrs.display?.innerContent?.phone?.value;
  const preset = displayDesktop?.preset || 'grid';
  const columnsDesktop = displayDesktop?.columns || '3';
  const columnsTablet = displayTablet?.columns || '2';
  const columnsPhone = displayPhone?.columns || '1';

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
        <strong>CC Search Results</strong>
        <span>
          {query?.source === 'isolated'
            ? `Isolated query: ${query.searchTerm || 'no search term'}`
            : 'Current WordPress search query'}
        </span>
        <small>
          {presetLabels[preset] || preset}
          {preset !== 'compact-list' ? ` · columns ${columnsDesktop}/${columnsTablet}/${columnsPhone}` : ''}
          {displayDesktop?.showSearchForm === 'off' ? ' · search form off' : ' · search form on'}
          {displayDesktop?.ajaxPagination === 'off' ? ' · classic pagination' : ' · AJAX pagination'}
        </small>
      </div>
      <ChildModulesContainer ids={childrenIds} />
    </ModuleContainer>
  );
};
