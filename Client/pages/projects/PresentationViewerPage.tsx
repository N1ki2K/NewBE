import React from 'react';
import { useParams } from 'react-router-dom';
import { getApiBaseUrl } from '../../src/utils/apiBaseUrl';
import { useLanguage } from '../../context/LanguageContext';

const PresentationViewerPage: React.FC = () => {
  const { filename = '' } = useParams<{ filename: string }>();
  const { getTranslation } = useLanguage();
  const decodedFilename = filename ? decodeURIComponent(filename) : '';
  const apiBaseUrl = getApiBaseUrl();
  const presentationUrl = `${apiBaseUrl}/Presentations/${encodeURIComponent(filename)}`;

  return (
    <div className="min-h-screen bg-gray-900 text-white flex flex-col">
      <header className="bg-gray-950 border-b border-gray-800">
        <div className="max-w-6xl mx-auto px-4 py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
          <div>
            <h1 className="text-xl font-semibold">{getTranslation('presentations.viewerTitle', 'Presentation Viewer')}</h1>
            <p className="text-sm text-gray-300">
              {getTranslation('presentations.current', 'Currently viewing:')}{' '}
              <span className="font-medium text-white">{decodedFilename || getTranslation('presentations.unknown', 'Unknown file')}</span>
            </p>
          </div>
          <div className="flex flex-wrap gap-2">
            <a
              href={presentationUrl}
              download={decodedFilename || undefined}
              className="px-3 py-2 bg-green-600 hover:bg-green-700 rounded-md text-sm font-medium transition-colors"
            >
              {getTranslation('presentations.download', 'Download')}
            </a>
            <button
              onClick={() => window.print()}
              className="px-3 py-2 bg-blue-600 hover:bg-blue-700 rounded-md text-sm font-medium transition-colors"
            >
              {getTranslation('presentations.print', 'Print')}
            </button>
            <button
              onClick={() => window.history.back()}
              className="px-3 py-2 bg-gray-700 hover:bg-gray-600 rounded-md text-sm font-medium transition-colors"
            >
              {getTranslation('presentations.back', 'Back')}
            </button>
          </div>
        </div>
      </header>

      <main className="flex-1 bg-black">
        <iframe
          src={presentationUrl}
          title={decodedFilename || getTranslation('presentations.viewerTitle', 'Presentation Viewer')}
          className="w-full h-full border-0"
          allow="fullscreen"
        />
      </main>

      <footer className="bg-gray-950 border-t border-gray-800 text-center text-xs text-gray-500 py-3">
        {getTranslation('presentations.footer', 'Use the download button above if the presentation does not display correctly.')}
      </footer>
    </div>
  );
};

export default PresentationViewerPage;
