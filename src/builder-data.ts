export interface PostTypeOption {
  label: string;
}

export type PostTypeOptions = Record<string, PostTypeOption>;

interface BuilderData {
  postTypes?: PostTypeOptions;
}

declare global {
  interface Window {
    ccD5srBuilderData?: BuilderData;
  }
}

interface MutableFieldComponent {
  name?: string;
  props?: {
    options?: PostTypeOptions;
    [key: string]: unknown;
  };
}

interface MutableModuleMetadata {
  title?: string;
  titles?: string;
  childModuleTitle?: string;
  attributes?: {
    rule?: {
      settings?: {
        innerContent?: {
          items?: {
            postType?: {
              component?: MutableFieldComponent;
            };
          };
        };
      };
    };
  };
}

const fallbackPostTypes: PostTypeOptions = {
  post: {
    label: 'Posts (post)',
  },
  page: {
    label: 'Pages (page)',
  },
};

export const getPostTypeOptions = (): PostTypeOptions => {
  const options = window.ccD5srBuilderData?.postTypes;

  if (! options || Object.keys(options).length === 0) {
    return fallbackPostTypes;
  }

  return options;
};

export const getPostTypeLabel = (postType: string): string => {
  return getPostTypeOptions()[postType]?.label || postType;
};

export const prepareSearchResultTypeMetadata = <T>(metadata: T): T => {
  const mutableMetadata = metadata as T & MutableModuleMetadata;
  const postTypeField = mutableMetadata.attributes?.rule?.settings?.innerContent?.items?.postType;

  mutableMetadata.title  = 'CC Result Type';
  mutableMetadata.titles = 'CC Result Types';

  if (postTypeField) {
    postTypeField.component = {
      ...postTypeField.component,
      name:  'divi/select',
      props: {
        ...postTypeField.component?.props,
        options: getPostTypeOptions(),
      },
    };
  }

  return metadata;
};

export const prepareSearchResultsMetadata = <T>(metadata: T): T => {
  const mutableMetadata = metadata as T & MutableModuleMetadata;

  mutableMetadata.title            = 'CC Search Results';
  mutableMetadata.titles           = 'CC Search Results';
  mutableMetadata.childModuleTitle = 'CC Result Type';

  return metadata;
};
