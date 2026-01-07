import React from 'react';
import ComponentCreator from '@docusaurus/ComponentCreator';

export default [
  {
    path: '/negarity-color/__docusaurus/debug',
    component: ComponentCreator('/negarity-color/__docusaurus/debug', '150'),
    exact: true
  },
  {
    path: '/negarity-color/__docusaurus/debug/config',
    component: ComponentCreator('/negarity-color/__docusaurus/debug/config', '367'),
    exact: true
  },
  {
    path: '/negarity-color/__docusaurus/debug/content',
    component: ComponentCreator('/negarity-color/__docusaurus/debug/content', '0b4'),
    exact: true
  },
  {
    path: '/negarity-color/__docusaurus/debug/globalData',
    component: ComponentCreator('/negarity-color/__docusaurus/debug/globalData', '9ee'),
    exact: true
  },
  {
    path: '/negarity-color/__docusaurus/debug/metadata',
    component: ComponentCreator('/negarity-color/__docusaurus/debug/metadata', 'f5e'),
    exact: true
  },
  {
    path: '/negarity-color/__docusaurus/debug/registry',
    component: ComponentCreator('/negarity-color/__docusaurus/debug/registry', 'c5d'),
    exact: true
  },
  {
    path: '/negarity-color/__docusaurus/debug/routes',
    component: ComponentCreator('/negarity-color/__docusaurus/debug/routes', '084'),
    exact: true
  },
  {
    path: '/negarity-color/docs',
    component: ComponentCreator('/negarity-color/docs', 'b65'),
    routes: [
      {
        path: '/negarity-color/docs',
        component: ComponentCreator('/negarity-color/docs', '468'),
        routes: [
          {
            path: '/negarity-color/docs',
            component: ComponentCreator('/negarity-color/docs', 'a9e'),
            routes: [
              {
                path: '/negarity-color/docs/basics/converting-colors',
                component: ComponentCreator('/negarity-color/docs/basics/converting-colors', '69e'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/negarity-color/docs/basics/creating-colors',
                component: ComponentCreator('/negarity-color/docs/basics/creating-colors', 'fdd'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/negarity-color/docs/basics/getting-channels',
                component: ComponentCreator('/negarity-color/docs/basics/getting-channels', '763'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/negarity-color/docs/basics/modifying-colors',
                component: ComponentCreator('/negarity-color/docs/basics/modifying-colors', 'b9a'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/negarity-color/docs/extending/color-names',
                component: ComponentCreator('/negarity-color/docs/extending/color-names', 'e3f'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/negarity-color/docs/extending/color-spaces',
                component: ComponentCreator('/negarity-color/docs/extending/color-spaces', '116'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/negarity-color/docs/extending/extractors',
                component: ComponentCreator('/negarity-color/docs/extending/extractors', '675'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/negarity-color/docs/extending/filters',
                component: ComponentCreator('/negarity-color/docs/extending/filters', '61e'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/negarity-color/docs/extractors-analysis/introduction',
                component: ComponentCreator('/negarity-color/docs/extractors-analysis/introduction', '757'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/negarity-color/docs/filters/blend',
                component: ComponentCreator('/negarity-color/docs/filters/blend', '32d'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/negarity-color/docs/filters/brightness',
                component: ComponentCreator('/negarity-color/docs/filters/brightness', 'b78'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/negarity-color/docs/filters/contrast',
                component: ComponentCreator('/negarity-color/docs/filters/contrast', '93d'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/negarity-color/docs/filters/difference',
                component: ComponentCreator('/negarity-color/docs/filters/difference', 'c3a'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/negarity-color/docs/filters/gamma',
                component: ComponentCreator('/negarity-color/docs/filters/gamma', '2cf'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/negarity-color/docs/filters/grayscale',
                component: ComponentCreator('/negarity-color/docs/filters/grayscale', '1c8'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/negarity-color/docs/filters/hue-rotate',
                component: ComponentCreator('/negarity-color/docs/filters/hue-rotate', '5b4'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/negarity-color/docs/filters/introduction',
                component: ComponentCreator('/negarity-color/docs/filters/introduction', '20f'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/negarity-color/docs/filters/invert',
                component: ComponentCreator('/negarity-color/docs/filters/invert', 'f04'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/negarity-color/docs/filters/mix',
                component: ComponentCreator('/negarity-color/docs/filters/mix', '525'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/negarity-color/docs/filters/posterize',
                component: ComponentCreator('/negarity-color/docs/filters/posterize', 'c93'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/negarity-color/docs/filters/saturation',
                component: ComponentCreator('/negarity-color/docs/filters/saturation', 'ed0'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/negarity-color/docs/filters/threshold',
                component: ComponentCreator('/negarity-color/docs/filters/threshold', '383'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/negarity-color/docs/getting-started',
                component: ComponentCreator('/negarity-color/docs/getting-started', '247'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/negarity-color/docs/intro',
                component: ComponentCreator('/negarity-color/docs/intro', 'cab'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/negarity-color/docs/mutability/introduction',
                component: ComponentCreator('/negarity-color/docs/mutability/introduction', '172'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/negarity-color/docs/named-colors/introduction',
                component: ComponentCreator('/negarity-color/docs/named-colors/introduction', '87c'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/negarity-color/docs/references/color-spaces',
                component: ComponentCreator('/negarity-color/docs/references/color-spaces', 'bac'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/negarity-color/docs/references/extractors',
                component: ComponentCreator('/negarity-color/docs/references/extractors', '702'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/negarity-color/docs/references/filters',
                component: ComponentCreator('/negarity-color/docs/references/filters', 'dca'),
                exact: true,
                sidebar: "tutorialSidebar"
              },
              {
                path: '/negarity-color/docs/references/methods',
                component: ComponentCreator('/negarity-color/docs/references/methods', '167'),
                exact: true,
                sidebar: "tutorialSidebar"
              }
            ]
          }
        ]
      }
    ]
  },
  {
    path: '*',
    component: ComponentCreator('*'),
  },
];
