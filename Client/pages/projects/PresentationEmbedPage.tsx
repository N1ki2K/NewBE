import React from 'react';
import { useParams } from 'react-router-dom';
import PageWrapper from '../../components/PageWrapper';
import { useLanguage } from '../../context/LanguageContext';
import { getApiBaseUrl } from '../../src/utils/apiBaseUrl';

const PresentationEmbedPage: React.FC = () => {
  const { filename = '' } = useParams<{ filename: string }>();
  const { getTranslation } = useLanguage();
  const baseTitle = getTranslation('presentations.embedTitle', 'Presentation Preview');
  const decodedFilename = filename ? decodeURIComponent(filename) : '';

  const apiBaseUrl = getApiBaseUrl();
  const presentationUrl = `${apiBaseUrl}/Presentations/${encodeURIComponent(filename)}`;

  return (
    <PageWrapper title={baseTitle}>
      <div className="space-y-6">
        <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <p className="text-blue-800">
            {getTranslation('presentations.current', 'Currently viewing:')}{' '}
            <span className="font-semibold">{decodedFilename || getTranslation('presentations.unknown', 'Unknown file')}</span>
          </p>
        </div>

        <div className="aspect-video w-full bg-gray-200 border border-gray-300 rounded-lg overflow-hidden shadow-inner">
          <iframe
            src={presentationUrl}
            title={decodedFilename || baseTitle}
            className="w-full h-full border-0"
            allow="fullscreen"
          />
        </div>

        <div className="flex flex-wrap gap-3">
          <a
            href={presentationUrl}
            target="_blank"
            rel="noopener noreferrer"
            className="inline-flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
          >
            <span>🔗</span>
            <span>{getTranslation('presentations.openNewTab', 'Open in new tab')}</span>
          </a>
          <a
            href={presentationUrl}
            download={decodedFilename || undefined}
            className="inline-flex items-center space-x-2 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors"
          >
            <span>⬇️</span>
            <span>{getTranslation('presentations.download', 'Download')}</span>
          </a>
        </div>
      </div>
    </PageWrapper>
  );
};

export default PresentationEmbedPage;
