import type { ClassValue } from 'clsx';
import { clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(route: any) {
    if (typeof route === 'function') {
        return typeof route.url === 'function' ? route.url() : route();
    }

    if (route && typeof route === 'object' && typeof route.url === 'string') {
        return route.url;
    }

    return route;
}
