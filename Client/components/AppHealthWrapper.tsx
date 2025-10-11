import React from 'react';
import useHealthCheck from '../hooks/useHealthCheck';

interface AppHealthWrapperProps {
  children: React.ReactNode;
}

const AppHealthWrapper: React.FC<AppHealthWrapperProps> = ({ children }) => {
  const { isHealthy, isLoading, error, retry } = useHealthCheck(true);

  // Show loading state while checking health
  if (isLoading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="w-12 h-12 border-4 border-brand-blue border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
          <p className="text-gray-600 text-lg">Loading system...</p>
        </div>
      </div>
    );
  }

  // If health check fails, log it but still render the app so the CMS is accessible.
  if (!isHealthy) {
    console.warn('Backend health check failed:', error);
  }

  return (
    <>
      {(!isHealthy && error) && (
        <div className="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 m-4 rounded">
          <p className="font-semibold">Внимание: неуспешна връзка със сървъра.</p>
          <button
            onClick={retry}
            className="mt-2 px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition"
            disabled={isLoading}
          >
            {isLoading ? 'Опитва се...' : 'Опитай отново'}
          </button>
        </div>
      )}
      {children}
    </>
  );
};

export default AppHealthWrapper;
