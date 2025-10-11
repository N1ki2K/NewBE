import { useMemo } from 'react';

import { bg } from '../../locales/bg';
import { en } from '../../locales/en';

const translationsByLang: Record<string, any> = {
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

const flattenedTranslations: Record<string, Record<string, string>> = {
  bg: flattenObject(bg),
  en: flattenObject(en),
};

export const useTranslationsSimple = (language: string = 'bg') => {
  const flat = flattenedTranslations[language] || {};
  const nested = translationsByLang[language] || {};

  const t = (keyPath: string, fallback?: string): string => {
    const value = flat[keyPath];
    if (value !== undefined) {
      return value;
    }
    return fallback !== undefined ? fallback : keyPath;
  };

  const getNestedTranslations = useMemo(() => nested, [nested]);

  return {
    translations: getNestedTranslations,
    flatTranslations: flat,
    loading: false,
    error: null,
    t,
  };
};

export default useTranslationsSimple;
