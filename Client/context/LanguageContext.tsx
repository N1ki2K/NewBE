import React, { createContext, useState, useContext, ReactNode } from 'react';
import en from '../locales/en';
import bg from '../locales/bg';

type Translations = typeof en;
type Language = 'en' | 'bg';

interface LanguageContextType {
  language: Language;
  setLanguage: (language: Language) => void;
  translations: Translations;
}

const LanguageContext = createContext<LanguageContextType | undefined>(undefined);

export const LanguageProvider = ({ children }: { children: ReactNode }) => {
  const [language, setLanguage] = useState<Language>('bg'); // Default language

  const translations = language === 'en' ? en : bg;

  return (
    <LanguageContext.Provider value={{ language, setLanguage, translations }}>
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

export const useTranslations = () => {
  const context = useContext(LanguageContext);
  if (!context) {
    throw new Error('useTranslations must be used within a LanguageProvider');
  }
  return context.translations;
};

// This function is defined here, so no import is needed.
export const useTranslationsSimple = () => {
  const context = useContext(LanguageContext);
  if (!context) {
    throw new Error('useTranslationsSimple must be used within a LanguageProvider');
  }
  return context.translations;
};