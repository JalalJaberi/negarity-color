import type {ReactNode} from 'react';
import {useEffect, useRef, useState} from 'react';
import type {ChangeEvent} from 'react';
import Link from '@docusaurus/Link';
import {useHistory} from '@docusaurus/router';
import useBaseUrl from '@docusaurus/useBaseUrl';

import styles from './index.module.css';

const CELL_SIZE = 10;
const CHANGES_PER_TICK = 25;

/** Auto-advance when average convergence reaches this (same as level 5 in the old scale). */
const AUTO_NAV_THRESHOLD_PERCENT = 80;

/** Max Euclidean distance in sRGB 0–255 space (black ↔ white diagonal). */
const MAX_RGB_DISTANCE = Math.sqrt(255 * 255 * 3);

type Rgb = {
  r: number;
  g: number;
  b: number;
};

function randomColor(): Rgb {
  return {
    r: Math.floor(Math.random() * 256),
    g: Math.floor(Math.random() * 256),
    b: Math.floor(Math.random() * 256),
  };
}

function toCssColor(color: Rgb): string {
  return `rgb(${color.r}, ${color.g}, ${color.b})`;
}

function toHex(color: Rgb): string {
  const part = (v: number): string => v.toString(16).padStart(2, '0');
  return `#${part(color.r)}${part(color.g)}${part(color.b)}`;
}

function fromHex(hex: string): Rgb {
  const clean = hex.replace('#', '');
  return {
    r: Number.parseInt(clean.slice(0, 2), 16),
    g: Number.parseInt(clean.slice(2, 4), 16),
    b: Number.parseInt(clean.slice(4, 6), 16),
  };
}

function moveTowardTarget(current: Rgb, target: Rgb): Rgb {
  return {
    r: Math.round(current.r + (target.r - current.r) * 0.5),
    g: Math.round(current.g + (target.g - current.g) * 0.5),
    b: Math.round(current.b + (target.b - current.b) * 0.5),
  };
}

function colorDistance(a: Rgb, b: Rgb): number {
  return Math.sqrt((a.r - b.r) ** 2 + (a.g - b.g) ** 2 + (a.b - b.b) ** 2);
}

function cellConversionRatio(cell: Rgb, goal: Rgb): number {
  const d = colorDistance(cell, goal);
  const ratio = 1 - d / MAX_RGB_DISTANCE;
  return Math.max(0, Math.min(1, ratio));
}

function gridConversionPercent(cells: Rgb[], goal: Rgb): number {
  if (cells.length === 0) return 0;
  let sum = 0;
  for (const c of cells) {
    sum += cellConversionRatio(c, goal);
  }
  return (sum / cells.length) * 100;
}

export default function Home(): ReactNode {
  const history = useHistory();
  const gettingStartedTo = useBaseUrl('/docs/getting-started');

  const mainRef = useRef<HTMLDivElement | null>(null);
  const [cellCount, setCellCount] = useState(0);
  const [cellColors, setCellColors] = useState<Rgb[]>([]);
  const [targetColor, setTargetColor] = useState<Rgb>(randomColor());
  const targetColorRef = useRef<Rgb>(targetColor);
  const [conversionPercent, setConversionPercent] = useState(0);
  const autoNavigatedForGoalRef = useRef<string>('');

  useEffect(() => {
    targetColorRef.current = targetColor;
  }, [targetColor]);

  useEffect(() => {
    const element = mainRef.current;
    if (!element) return;

    const cols = Math.max(1, Math.ceil(element.clientWidth / CELL_SIZE));
    const rows = Math.max(1, Math.ceil(element.clientHeight / CELL_SIZE));
    const nextCount = cols * rows;

    setCellCount(nextCount);
    setCellColors(Array.from({length: nextCount}, () => randomColor()));
  }, []);

  useEffect(() => {
    if (cellCount === 0) return;

    const timer = window.setInterval(() => {
      setCellColors((prev) => {
        if (prev.length === 0) return prev;
        const next = [...prev];
        const changes = Math.min(CHANGES_PER_TICK, next.length);
        const used = new Set<number>();

        while (used.size < changes) {
          used.add(Math.floor(Math.random() * next.length));
        }

        const goal = targetColorRef.current;
        for (const index of used) {
          next[index] = moveTowardTarget(next[index], goal);
        }

        return next;
      });
    }, 50);

    return () => window.clearInterval(timer);
  }, [cellCount]);

  useEffect(() => {
    if (cellColors.length === 0) return;
    setConversionPercent(gridConversionPercent(cellColors, targetColor));
  }, [cellColors, targetColor]);

  const goalKey = `${targetColor.r},${targetColor.g},${targetColor.b}`;

  useEffect(() => {
    autoNavigatedForGoalRef.current = '';
  }, [goalKey]);

  useEffect(() => {
    if (conversionPercent < AUTO_NAV_THRESHOLD_PERCENT) return;
    if (autoNavigatedForGoalRef.current === goalKey) return;
    autoNavigatedForGoalRef.current = goalKey;
    history.push(gettingStartedTo);
  }, [conversionPercent, goalKey, gettingStartedTo, history]);

  const onTargetColorChange = (event: ChangeEvent<HTMLInputElement>): void => {
    const next = fromHex(event.target.value);
    setTargetColor(next);
  };

  const percentLabel =
    conversionPercent < 10
      ? conversionPercent.toFixed(1)
      : conversionPercent.toFixed(0);

  const remainingToThreshold = Math.max(
    0,
    AUTO_NAV_THRESHOLD_PERCENT - conversionPercent,
  );

  return (
    <div ref={mainRef} className={styles.main}>
      <div className={styles.pixelGrid}>
        {cellColors.map((color, index) => (
          <div
            key={index}
            className={styles.pixelCell}
            style={{backgroundColor: toCssColor(color)}}
          />
        ))}
      </div>
      <div className={styles.centerButton}>
        <input
          className={styles.colorPicker}
          type="color"
          aria-label="Pick final color"
          value={toHex(targetColor)}
          onChange={onTargetColorChange}
        />

        <div className={styles.readout} aria-live="polite">
          <span className={styles.readoutLabel}>Converged toward goal</span>
          <span className={styles.readoutValue}>
            {conversionPercent < 10
              ? conversionPercent.toFixed(1)
              : conversionPercent.toFixed(0)}
            %
          </span>
          <span className={styles.readoutMeta}>
            <strong className={styles.readoutThreshold}>
              Threshold: {AUTO_NAV_THRESHOLD_PERCENT}% average convergence
            </strong>
            <span className={styles.readoutHint}>
              Cross that value and this page opens Getting Started automatically.
            </span>
            {conversionPercent < AUTO_NAV_THRESHOLD_PERCENT && (
              <span className={styles.readoutRemaining}>
                Remaining until threshold:{' '}
                {remainingToThreshold < 10
                  ? remainingToThreshold.toFixed(1)
                  : Math.ceil(remainingToThreshold)}
                %
              </span>
            )}
          </span>
        </div>

        <Link
          className={`button button--secondary button--lg ${styles.ctaButton}`}
          to={gettingStartedTo}
          aria-label={`Get Started — ${percentLabel}% converged; ${AUTO_NAV_THRESHOLD_PERCENT}% opens the tutorial automatically`}>
          Get Started{' '}
          <span className={styles.buttonPercent} aria-hidden="true">
            · {percentLabel}%
          </span>
        </Link>
      </div>
    </div>
  );
}
