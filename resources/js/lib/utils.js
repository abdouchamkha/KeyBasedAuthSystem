import { clsx } from "clsx";
import { twMerge } from "tailwind-merge";


export function debounce(fn, delay) {
    let timeoutId;
    return function (...args) {
      clearTimeout(timeoutId);
      timeoutId = setTimeout(() => fn.apply(this, args), delay);
    };
  }

export function cn(...inputs) {
  return twMerge(clsx(inputs));
}
export function valueUpdater(updaterOrValue, ref) {
    ref.value = typeof updaterOrValue === 'function'
      ? updaterOrValue(ref.value)
      : updaterOrValue
  }
