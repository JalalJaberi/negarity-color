import type {ReactNode} from 'react';
import clsx from 'clsx';
import Heading from '@theme/Heading';
import styles from './styles.module.css';

type FeatureItem = {
  title: string;
  image: string;
  description: ReactNode;
};

const FeatureList: FeatureItem[] = [
  {
    title: 'Intuitive',
    image: require('@site/static/img/negarity-color-doc-home-1.png').default,
    description: (
      <>
        negarity color provides a simple and intuitive API for color manipulation and conversion.
      </>
    ),
  },
  {
    title: 'Extencible',
    image: require('@site/static/img/negarity-color-doc-home-2.png').default,
    description: (
      <>
        negarity color is built with extensibility in mind. You can easily add new color spaced and color manipulations.
      </>
    ),
  },
  {
    title: 'Modern PHP',
    image: require('@site/static/img/negarity-color-doc-home-3.png').default,
    description: (
      <>
        negarity color leverages modern PHP features like type declarations and namespaces to provide a robust and reliable experience.
      </>
    ),
  },
];

function Feature({title, image, description}: FeatureItem) {
  return (
    <div className={clsx('col col--4')}>
      <div className="text--center">
        <img src={image} alt="" className={styles.featureSvg} role="img" />
      </div>
      <div className="text--center padding-horiz--md">
        <Heading as="h3">{title}</Heading>
        <p>{description}</p>
      </div>
    </div>
  );
}

export default function HomepageFeatures(): ReactNode {
  return (
    <section className={styles.features}>
      <div className="container">
        <div className="row">
          {FeatureList.map((props, idx) => (
            <Feature key={idx} {...props} />
          ))}
        </div>
      </div>
    </section>
  );
}
