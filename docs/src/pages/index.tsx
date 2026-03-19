import type {ReactNode} from 'react';
import {useEffect, useRef, useState} from 'react';
import type {ChangeEvent} from 'react';
import Link from '@docusaurus/Link';

import styles from './index.module.css';

const CELL_SIZE = 10;
const CHANGES_PER_TICK = 25;

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

export default function Home(): ReactNode {
  const mainRef = useRef<HTMLDivElement | null>(null);
  const [cellCount, setCellCount] = useState(0);
  const [cellColors, setCellColors] = useState<Rgb[]>([]);
  const [targetColor, setTargetColor] = useState<Rgb>(randomColor());
  const targetColorRef = useRef<Rgb>(targetColor);

  useEffect(() => {
    targetColorRef.current = targetColor;
  }, [targetColor]);

  useEffect(() => {
    const element = mainRef.current;
    if (!element) return;

    // Create the grid once on mount.
    // After that, we only change colors (as requested).
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

        for (const index of used) {
          next[index] = moveTowardTarget(next[index], targetColorRef.current);
        }

        return next;
      });
    }, 50);

    return () => window.clearInterval(timer);
  }, [cellCount]);

  const onTargetColorChange = (event: ChangeEvent<HTMLInputElement>): void => {
    const next = fromHex(event.target.value);
    setTargetColor(next);
  };

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
        <Link className="button button--secondary button--lg" to="/docs/getting-started">
          Get Started
        </Link>
      </div>
    </div>
  );
}
