import React, { ReactElement } from 'react';
import { StyleContainer, StylesProps } from '@divi/module';

import { SearchResultsAttrs } from './types';

export const ModuleStyles = ({
  elements,
  mode,
  noStyleTag,
  settings,
  state,
}: StylesProps<SearchResultsAttrs>): ReactElement => (
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
