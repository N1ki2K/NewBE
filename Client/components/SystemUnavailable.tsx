import React from 'react';
import { useLanguage } from '../context/LanguageContext';

interface SystemUnavailableProps {
  onRetry: () => void;
  isRetrying?: boolean;
  error?: string;
}

const SystemUnavailable: React.FC<SystemUnavailableProps> = () => {
  return null;
};

export default SystemUnavailable;
