import React, { useEffect, useMemo, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import PageWrapper from '../components/PageWrapper';
import InlinePDFViewer from '../components/InlinePDFViewer';
import { useLanguage } from '../context/LanguageContext';
import { apiService, ApiError } from '../src/services/api';
import { getApiBaseUrl } from '../src/utils/apiBaseUrl';

interface DocumentItem {
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

const DocumentsPage: React.FC = () => {
  const { getTranslation } = useLanguage();
  const [documents, setDocuments] = useState<DocumentItem[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [selectedDocument, setSelectedDocument] = useState<DocumentItem | null>(null);
  const [searchParams, setSearchParams] = useSearchParams();

  const downloadBaseUrl = useMemo(() => `${getApiBaseUrl()}/public/uploads/documents/`, []);

  useEffect(() => {
    const loadDocuments = async () => {
      try {
        setIsLoading(true);
        setError(null);
        const response = await apiService.getDocuments();
        const docs = Array.isArray(response.documents) ? response.documents : [];
        setDocuments(docs);
      } catch (err) {
        console.error('Failed to load documents:', err);
        if (err instanceof ApiError && err.status === 0) {
          setError(getTranslation('documentsPage.error.connection', 'Неуспешна връзка със сървъра.'));
        } else if (err instanceof Error) {
          setError(err.message);
        } else {
          setError(getTranslation('documentsPage.error.generic', 'Възникна грешка при зареждането на документите.'));
        }
        setDocuments([]);
      } finally {
        setIsLoading(false);
      }
    };

    loadDocuments();
  }, [getTranslation]);

  useEffect(() => {
    if (documents.length === 0) {
      setSelectedDocument(null);
      return;
    }

    const fileParam = searchParams.get('file');
    if (fileParam) {
      const decoded = decodeURIComponent(fileParam);
      const match = documents.find((doc) => doc.filename === decoded);
      if (match) {
        setSelectedDocument(match);
        return;
      }
    }

    setSelectedDocument(documents[0]);
  }, [documents, searchParams]);

  const handleSelectDocument = (doc: DocumentItem) => {
    setSelectedDocument(doc);
    setSearchParams({ file: doc.filename });
  };

  const isPdf = (filename: string) => filename.toLowerCase().endsWith('.pdf');

  const renderViewer = () => {
    if (!selectedDocument) {
      return null;
    }

    if (isPdf(selectedDocument.filename)) {
      return (
        <InlinePDFViewer
          key={selectedDocument.filename}
          filename={selectedDocument.filename}
          title={selectedDocument.filename}
        />
      );
    }

    return (
      <div className="bg-white border border-gray-200 rounded-lg p-6">
        <h3 className="text-lg font-semibold text-brand-blue mb-3">
          {selectedDocument.filename}
        </h3>
        <p className="text-gray-600 mb-4">
          {getTranslation(
            'documentsPage.previewUnavailable',
            'Прегледът не е наличен за този файл. Моля, изтеглете го, за да го разгледате.'
          )}
        </p>
        <a
          href={`${downloadBaseUrl}${encodeURIComponent(selectedDocument.filename)}`}
          target="_blank"
          rel="noopener noreferrer"
          className="inline-flex items-center gap-2 px-4 py-2 bg-brand-blue text-white rounded-md hover:bg-brand-blue-light transition-colors"
        >
          {getTranslation('documentsPage.download', 'Изтегли файл')}
        </a>
      </div>
    );
  };

  return (
    <PageWrapper title={getTranslation('documentsPage.title', 'Документи')}>
      <div className="space-y-8">
        <p className="text-lg text-gray-600">
          {getTranslation(
            'documentsPage.description',
            'Разгледайте и изтеглете всички налични документи на училището. Кликнете върху документ, за да го визуализирате.'
          )}
        </p>

        {isLoading && (
          <div className="bg-white border border-gray-200 rounded-lg p-6 text-center">
            <div className="animate-spin h-8 w-8 border-b-2 border-brand-blue mx-auto mb-4 rounded-full" />
            <p className="text-gray-600">
              {getTranslation('documentsPage.loading', 'Зареждане на документи...')}
            </p>
          </div>
        )}

        {error && (
          <div className="bg-red-50 border border-red-200 rounded-lg p-6">
            <h3 className="text-lg font-semibold text-red-700 mb-2">
              {getTranslation('documentsPage.error.title', 'Възникна грешка')}
            </h3>
            <p className="text-red-600 mb-4">{error}</p>
            <button
              onClick={() => window.location.reload()}
              className="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors"
            >
              {getTranslation('documentsPage.retry', 'Опитай отново')}
            </button>
          </div>
        )}

        {!isLoading && !error && documents.length === 0 && (
          <div className="bg-white border border-gray-200 rounded-lg p-6 text-center text-gray-600">
            {getTranslation('documentsPage.empty', 'Няма налични документи в момента.')}
          </div>
        )}

        {!isLoading && !error && documents.length > 0 && (
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div className="space-y-3">
              {documents.map((doc) => {
                const isActive = selectedDocument?.filename === doc.filename;
                return (
                  <button
                    key={doc.filename}
                    onClick={() => handleSelectDocument(doc)}
                    className={`w-full text-left rounded-lg border p-4 transition-colors ${
                      isActive
                        ? 'border-brand-blue bg-brand-blue/10 text-brand-blue'
                        : 'border-gray-200 bg-white hover:border-brand-blue/40 hover:bg-brand-blue/5'
                    }`}
                  >
                    <p className="font-semibold truncate">{doc.filename}</p>
                    <div className="mt-2 flex items-center justify-between text-sm text-gray-500">
                      <span>{formatBytes(doc.size)}</span>
                      <span>
                        {getTranslation('documentsPage.updated', 'Обновен на')}{' '}
                        {new Date(doc.modified * 1000).toLocaleDateString()}
                      </span>
                    </div>
                  </button>
                );
              })}
            </div>

            <div className="lg:col-span-2 space-y-4">
              {selectedDocument && (
                <div className="flex items-center justify-between bg-white border border-gray-200 rounded-lg p-4">
                  <div>
                    <h2 className="text-xl font-semibold text-brand-blue">
                      {getTranslation('documentsPage.currentDocument', 'Избран документ')}
                    </h2>
                    <p className="text-gray-600 truncate">{selectedDocument.filename}</p>
                  </div>
                  <a
                    href={`${downloadBaseUrl}${encodeURIComponent(selectedDocument.filename)}`}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="inline-flex items-center gap-2 px-4 py-2 bg-brand-blue text-white rounded-md hover:bg-brand-blue-light transition-colors"
                  >
                    {getTranslation('documentsPage.download', 'Изтегли файл')}
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

export default DocumentsPage;
