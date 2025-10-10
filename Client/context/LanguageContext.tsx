import React, { createContext, useState, useContext, ReactNode, useMemo, useCallback } from 'react';
import { en } from '../locales/en';
import { bg } from '../locales/bg';
import { useTranslationsSimple } from '../src/hooks/useTranslationsSimple';

type Translations = typeof en;
type Language = 'en' | 'bg';

interface LanguageContextType {
  language: Language;
  locale: Language;
  setLanguage: (language: Language) => void;
  translations: Translations;
  t: Translations;
  getTranslation: (key: string, fallback?: string) => string;
  refreshTranslations: (lang?: Language) => Promise<void>;
}

const LanguageContext = createContext<LanguageContextType | undefined>(undefined);

export const LanguageProvider = ({ children }: { children: ReactNode }) => {
  const [language, setLanguage] = useState<Language>('bg'); // Default language
  const {
    translations: dynamicTranslations,
    refreshTranslations
  } = useTranslationsSimple(language);

  const baseTranslations = language === 'en' ? en : bg;

  const mergedTranslations = useMemo<Translations>(() => {
    return {
      ...baseTranslations,
      ...(dynamicTranslations || {})
    } as Translations;
  }, [baseTranslations, dynamicTranslations]);

  const getTranslation = useCallback(
    (keyPath: string, fallback?: string) => {
      const keys = keyPath.split('.');
      let current: any = mergedTranslations;

      for (const key of keys) {
        if (current && typeof current === 'object' && key in current) {
          current = current[key];
        } else {
          return fallback ?? keyPath;
        }
      }

      return typeof current === 'string' ? current : (fallback ?? keyPath);
    },
    [mergedTranslations]
  );

  return (
    <LanguageContext.Provider
      value={{
        language,
        locale: language,
        setLanguage,
        translations: mergedTranslations,
        t: mergedTranslations,
        getTranslation,
        refreshTranslations: (targetLang?: Language) => refreshTranslations(targetLang)
      }}
    >
      {children}
    </LanguageContext.Provider>
  );
};

export const useLanguage = () => {
  const context = useContext(LanguageContext);
  if (!context) {
    throw new Error('useLanguage must be used within a LanguageProvider');
  }
  return context;
};

export const useLanguageTranslations = () => {
  const context = useContext(LanguageContext);
  if (!context) {
    throw new Error('useLanguageTranslations must be used within a LanguageProvider');
  }
  return context.translations;
};

export const useLanguageGetTranslation = () => {
  const context = useContext(LanguageContext);
  if (!context) {
    throw new Error('useLanguageGetTranslation must be used within a LanguageProvider');
  }
  return context.getTranslation;
};
