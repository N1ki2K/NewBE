const LOCAL_FALLBACK = 'http://localhost:3001';

const normalizeBaseUrl = (url: string): string => url.replace(/\/+$/, '');

export const getApiBaseUrl = (): string => {
  const envUrl = import.meta.env.VITE_API_URL;
  if (envUrl && typeof envUrl === 'string') {
    return normalizeBaseUrl(envUrl);
  }

  if (typeof window !== 'undefined' && window.location) {
    const { origin, hostname } = window.location;

    if (hostname === 'localhost' || hostname === '127.0.0.1') {
      return normalizeBaseUrl(LOCAL_FALLBACK);
    }

    return normalizeBaseUrl(`${origin}/backend`);
  }

  return normalizeBaseUrl(LOCAL_FALLBACK);
};

export const getApiEndpointBase = (): string => `${getApiBaseUrl()}/api`;
