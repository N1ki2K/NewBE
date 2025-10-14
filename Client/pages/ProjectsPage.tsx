import React, { useEffect, useMemo, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import PageWrapper from '../components/PageWrapper';
import InlinePDFViewer from '../components/InlinePDFViewer';
import { useLanguage } from '../context/LanguageContext';
import { apiService, ApiError } from '../src/services/api';
import { getApiBaseUrl } from '../src/utils/apiBaseUrl';

interface PresentationItem {
  filename: string;
  url: string;
  size: number;
  modified: number;
}

const formatBytes = (bytes: number): string => {
  if (!Number.isFinite(bytes) || bytes <= 0) return '0 KB';
  const units = ['B', 'KB', 'MB', 'GB', 'TB'];
  const i = Math.floor(Math.log(bytes) / Math.log(1024));
  const value = bytes / Math.pow(1024, i);
  return `${value.toFixed(value >= 10 || i === 0 ? 0 : 1)} ${units[i]}`;
};

const ProjectsPage: React.FC = () => {
  const { getTranslation } = useLanguage();
  const [presentations, setPresentations] = useState<PresentationItem[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [selectedPresentation, setSelectedPresentation] = useState<PresentationItem | null>(null);
  const [searchParams, setSearchParams] = useSearchParams();

  const downloadBaseUrl = useMemo(() => `${getApiBaseUrl()}/public/uploads/presentations/`, []);

  useEffect(() => {
    const loadPresentations = async () => {
      try {
        setIsLoading(true);
        setError(null);
        const response = await apiService.getPresentations();
        const list = Array.isArray(response.presentations) ? response.presentations : [];
        setPresentations(list);
      } catch (err) {
        console.error('Failed to load presentations:', err);
        if (err instanceof ApiError && err.status === 0) {
          setError(getTranslation('projectsPage.error.connection', 'Неуспешна връзка със сървъра.'));
        } else if (err instanceof Error) {
          setError(err.message);
        } else {
          setError(getTranslation('projectsPage.error.generic', 'Възникна грешка при зареждането на презентациите.'));
        }
        setPresentations([]);
      } finally {
        setIsLoading(false);
      }
    };

    loadPresentations();
  }, [getTranslation]);

  useEffect(() => {
    if (presentations.length === 0) {
      setSelectedPresentation(null);
      return;
    }

    const fileParam = searchParams.get('file');
    if (fileParam) {
      const decoded = decodeURIComponent(fileParam);
      const match = presentations.find((item) => item.filename === decoded);
      if (match) {
        setSelectedPresentation(match);
        return;
      }
    }

    setSelectedPresentation(presentations[0]);
  }, [presentations, searchParams]);

  const handleSelectPresentation = (item: PresentationItem) => {
    setSelectedPresentation(item);
    setSearchParams({ file: item.filename });
  };

  const isPdf = (filename: string) => filename.toLowerCase().endsWith('.pdf');

  const isPowerPoint = (filename: string) => {
    const lower = filename.toLowerCase();
    return lower.endsWith('.ppt') || lower.endsWith('.pptx');
  };

  const renderViewer = () => {
    if (!selectedPresentation) {
      return null;
    }

    if (isPdf(selectedPresentation.filename)) {
      return (
        <InlinePDFViewer
          key={selectedPresentation.filename}
          filename={selectedPresentation.filename}
          title={selectedPresentation.filename}
        />
      );
    }

    if (isPowerPoint(selectedPresentation.filename)) {
      const fileUrl = `${downloadBaseUrl}${encodeURIComponent(selectedPresentation.filename)}`;
      const viewerUrl = `https://view.officeapps.live.com/op/embed.aspx?src=${encodeURIComponent(fileUrl)}`;

      return (
        <div className="bg-white border border-gray-200 rounded-lg overflow-hidden">
          <iframe
            key={viewerUrl}
            src={viewerUrl}
            title={selectedPresentation.filename}
            className="w-full"
            style={{ minHeight: '600px' }}
            allowFullScreen
          />
        </div>
      );
    }

    return (
      <div className="bg-white border border-gray-200 rounded-lg p-6">
        <h3 className="text-lg font-semibold text-brand-blue mb-3">
          {selectedPresentation.filename}
        </h3>
        <p className="text-gray-600 mb-4">
          {getTranslation(
            'projectsPage.previewUnavailable',
            'Прегледът не е наличен за този файл. Моля, изтеглете го, за да го разгледате.'
          )}
        </p>
        <a
          href={`${downloadBaseUrl}${encodeURIComponent(selectedPresentation.filename)}`}
          target="_blank"
          rel="noopener noreferrer"
          className="inline-flex items-center gap-2 px-4 py-2 bg-brand-blue text-white rounded-md hover:bg-brand-blue-light transition-colors"
        >
          {getTranslation('projectsPage.download', 'Изтегли файл')}
        </a>
      </div>
    );
  };

  return (
    <PageWrapper title={getTranslation('projectsPage.title', 'Проекти')}>
      <div className="space-y-8">
        {isLoading && (
          <div className="bg-white border border-gray-200 rounded-lg p-6 text-center">
            <div className="animate-spin h-8 w-8 border-b-2 border-brand-blue mx-auto mb-4 rounded-full" />
            <p className="text-gray-600">
              {getTranslation('projectsPage.loading', 'Зареждане на презентации...')}
            </p>
          </div>
        )}

        {error && (
          <div className="bg-red-50 border border-red-200 rounded-lg p-6">
            <h3 className="text-lg font-semibold text-red-700 mb-2">
              {getTranslation('projectsPage.error.title', 'Възникна грешка')}
            </h3>
            <p className="text-red-600 mb-4">{error}</p>
            <button
              onClick={() => window.location.reload()}
              className="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors"
            >
              {getTranslation('projectsPage.retry', 'Опитай отново')}
            </button>
          </div>
        )}

        {!isLoading && !error && presentations.length === 0 && (
          <div className="bg-white border border-gray-200 rounded-lg p-6 text-center text-gray-600">
            {getTranslation('projectsPage.empty', 'Няма налични презентации в момента.')}
          </div>
        )}

        {!isLoading && !error && presentations.length > 0 && (
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div className="space-y-3">
              {presentations.map((item) => {
                const isActive = selectedPresentation?.filename === item.filename;
                return (
                  <button
                    key={item.filename}
                    onClick={() => handleSelectPresentation(item)}
                    className={`w-full text-left rounded-lg border p-4 transition-colors ${
                      isActive
                        ? 'border-brand-blue bg-brand-blue/10 text-brand-blue'
                        : 'border-gray-200 bg-white hover:border-brand-blue/40 hover:bg-brand-blue/5'
                    }`}
                  >
                    <p className="font-semibold truncate">{item.filename}</p>
                    <div className="mt-2 flex items-center justify-between text-sm text-gray-500">
                      <span>{formatBytes(item.size)}</span>
                      <span>
                        {getTranslation('projectsPage.updated', 'Обновена на')}{' '}
                        {new Date(item.modified * 1000).toLocaleDateString()}
                      </span>
                    </div>
                  </button>
                );
              })}
            </div>

            <div className="lg:col-span-2 space-y-4">
              {selectedPresentation && (
                <div className="flex items-center justify-between bg-white border border-gray-200 rounded-lg p-4">
                  <div>
                    <h2 className="text-xl font-semibold text-brand-blue">
                      {getTranslation('projectsPage.currentPresentation', 'Избрана презентация')}
                    </h2>
                    <p className="text-gray-600 truncate">{selectedPresentation.filename}</p>
                  </div>
                  <a
                    href={`${downloadBaseUrl}${encodeURIComponent(selectedPresentation.filename)}`}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="inline-flex items-center gap-2 px-4 py-2 bg-brand-blue text-white rounded-md hover:bg-brand-blue-light transition-colors"
                  >
                    {getTranslation('projectsPage.download', 'Изтегли файл')}
                  </a>
                </div>
              )}

              {renderViewer()}
            </div>
          </div>
        )}

      </div>
    </PageWrapper>
  );
};

export default ProjectsPage;
