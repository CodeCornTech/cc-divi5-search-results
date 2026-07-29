import React, { ReactElement } from 'react';
import { StyleContainer, StylesProps } from '@divi/module';

import { SearchResultsAttrs } from './types';

export const ModuleStyles = (props: StylesProps<SearchResultsAttrs>): ReactElement => {
  const {
    elements,
    mode,
    noStyleTag,
    settings,
    state,
  } = props;

  if (! elements || ! mode || ! state) {
    return <></>;
  }

  return (
    <StyleContainer mode={mode} state={state} noStyleTag={noStyleTag}>
      {elements.style({
        attrName: 'module',
        styleProps: {
          disabledOn: {
            disabledModuleVisibility: settings?.disabledModuleVisibility,
          },
        },
      })}
    </StyleContainer>
  );
};
