import { useMemo, useCallback } from 'react';
import { bg } from '../../locales/bg';
import { en } from '../../locales/en';

const rawTranslations: Record<string, any> = {
  bg,
  en,
};

const flattenObject = (obj: any, prefix = '', result: Record<string, string> = {}): Record<string, string> => {
  if (!obj || typeof obj !== 'object') {
    return result;
  }

  Object.keys(obj).forEach((key) => {
    const value = obj[key];
    const path = prefix ? `${prefix}.${key}` : key;

    if (value && typeof value === 'object' && !Array.isArray(value)) {
      flattenObject(value, path, result);
    } else if (value != null) {
      result[path] = String(value);
    }
  });

  return result;
};

const flattenedCache: Record<string, Record<string, string>> = {
  bg: flattenObject(bg),
  en: flattenObject(en),
};

export const useTranslations = (language: string = 'bg') => {
  const flatTranslations = flattenedCache[language] || {};
  const nestedTranslations = rawTranslations[language] || {};

  const t = useCallback(
    (keyPath: string, fallback?: string) => {
      const value = flatTranslations[keyPath];
      if (value !== undefined) {
        return value;
      }
      return fallback !== undefined ? fallback : keyPath;
    },
    [flatTranslations]
  );

  const translations = useMemo(() => nestedTranslations, [nestedTranslations]);

  const refreshTranslations = useCallback(async () => {
    // No-op: translations are bundled statically in this build.
  }, []);

  return {
    translations,
    flatTranslations,
    loading: false,
    error: null,
    t,
    refreshTranslations,
  };
};

export default useTranslations;
