import React, { ReactElement } from 'react';
import { ModuleContainer } from '@divi/module';

import { getPostTypeLabel } from '../../builder-data';
import { SearchResultTypeEditProps } from './types';

export const SearchResultTypeEdit = (props: SearchResultTypeEditProps): ReactElement => {
  const { attrs, elements, id, name } = props;
  const rule = attrs.rule?.innerContent?.desktop?.value;
  const postType = rule?.postType || 'post';
  const postTypeLabel = getPostTypeLabel(postType);

  return (
    <ModuleContainer attrs={attrs} elements={elements} id={id} name={name} tag="div">
      <div className="cc-d5sr-rule-vb" style={{ borderColor: rule?.accentColor || '#2b2f36' }}>
        <strong>CC Result Type — {postTypeLabel}</strong>
        <code>{postType}</code>
      </div>
    </ModuleContainer>
  );
};
