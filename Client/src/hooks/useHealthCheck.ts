import { useCallback } from 'react';

interface UseHealthCheckReturn {
  isHealthy: boolean;
  isLoading: boolean;
  error: string | null;
  retry: () => Promise<void>;
  appKey: string | null;
}

export const useHealthCheck = (_autoCheck: boolean = true): UseHealthCheckReturn => {
  const retry = useCallback(async () => {
    // No-op: health checks are disabled in this build.
  }, []);

  return {
    isHealthy: true,
    isLoading: false,
    error: null,
    retry,
    appKey: null,
  };
};

export default useHealthCheck;
