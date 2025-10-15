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
  isDirectory?: boolean;
  path?: string;
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
  const [currentPath, setCurrentPath] = useState<string>('');

  const downloadBaseUrl = useMemo(() => `${getApiBaseUrl()}/public/uploads/documents/`, []);

  // Parse path from URL params
  useEffect(() => {
    const pathParam = searchParams.get('path');
    setCurrentPath(pathParam || '');
  }, [searchParams]);

  // Load documents/folders based on current path
  useEffect(() => {
    const loadDocuments = async () => {
      try {
        setIsLoading(true);
        setError(null);
        const response = await apiService.getDocuments();
        const allDocs = Array.isArray(response.documents) ? response.documents : [];

        // Group items by current path
        const items = processDocumentsForPath(allDocs, currentPath);
        setDocuments(items);
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
  }, [getTranslation, currentPath]);

  // Process documents to show folders and files for current path
  const processDocumentsForPath = (allDocs: DocumentItem[], path: string): DocumentItem[] => {
    const items: DocumentItem[] = [];
    const folders = new Set<string>();

    const prefix = path ? `${path}/` : '';
    const prefixLength = prefix.length;

    allDocs.forEach((doc) => {
      const filename = doc.filename;

      // Check if document is in current path or subpath
      if (!path || filename.startsWith(prefix)) {
        const relativePath = path ? filename.substring(prefixLength) : filename;
        const slashIndex = relativePath.indexOf('/');

        if (slashIndex > 0) {
          // It's in a subfolder
          const folderName = relativePath.substring(0, slashIndex);
          if (!folders.has(folderName)) {
            folders.add(folderName);
            items.push({
              filename: folderName,
              url: '',
              size: 0,
              modified: 0,
              isDirectory: true,
              path: path ? `${path}/${folderName}` : folderName,
            });
          }
        } else if (relativePath) {
          // It's a file in the current path
          items.push({
            ...doc,
            isDirectory: false,
            path: filename,
          });
        }
      }
    });

    // Sort: folders first, then files
    return items.sort((a, b) => {
      if (a.isDirectory && !b.isDirectory) return -1;
      if (!a.isDirectory && b.isDirectory) return 1;
      return a.filename.localeCompare(b.filename);
    });
  };

  useEffect(() => {
    if (documents.length === 0) {
      setSelectedDocument(null);
      return;
    }

    const fileParam = searchParams.get('file');
    if (fileParam) {
      const decoded = decodeURIComponent(fileParam);
      const match = documents.find((doc) => doc.filename === decoded && !doc.isDirectory);
      if (match) {
        setSelectedDocument(match);
        return;
      }
    }

    // Auto-select first file (not folder)
    const firstFile = documents.find((doc) => !doc.isDirectory);
    setSelectedDocument(firstFile || null);
  }, [documents, searchParams]);

  const handleSelectItem = (item: DocumentItem) => {
    if (item.isDirectory) {
      // Navigate into folder
      const newPath = item.path || item.filename;
      setSearchParams({ path: newPath });
      setSelectedDocument(null);
    } else {
      // Select file for preview
      setSelectedDocument(item);
      const params: any = { file: item.filename };
      if (currentPath) {
        params.path = currentPath;
      }
      setSearchParams(params);
    }
  };

  const navigateToPath = (path: string) => {
    if (path) {
      setSearchParams({ path });
    } else {
      setSearchParams({});
    }
    setSelectedDocument(null);
  };

  const getBreadcrumbs = () => {
    if (!currentPath) return [];
    const parts = currentPath.split('/');
    const breadcrumbs: { name: string; path: string }[] = [];
    let accumulatedPath = '';

    parts.forEach((part, index) => {
      accumulatedPath = index === 0 ? part : `${accumulatedPath}/${part}`;
      breadcrumbs.push({ name: part, path: accumulatedPath });
    });

    return breadcrumbs;
  };

  const isPdf = (filename: string) => filename.toLowerCase().endsWith('.pdf');

  const renderViewer = () => {
    if (!selectedDocument) {
      return (
        <div className="bg-gray-50 border border-gray-200 rounded-lg p-12 text-center">
          <div className="text-6xl mb-4">📂</div>
          <h3 className="text-lg font-semibold text-gray-700 mb-2">
            {getTranslation('documentsPage.noSelection', 'Няма избран документ')}
          </h3>
          <p className="text-gray-500">
            {getTranslation('documentsPage.selectDocument', 'Изберете документ или папка от лявата страна')}
          </p>
        </div>
      );
    }

    const fullPath = selectedDocument.path || selectedDocument.filename;

    if (isPdf(selectedDocument.filename)) {
      return (
        <InlinePDFViewer
          key={fullPath}
          filename={fullPath}
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
          href={`${downloadBaseUrl}${encodeURIComponent(fullPath)}`}
          target="_blank"
          rel="noopener noreferrer"
          className="inline-flex items-center gap-2 px-4 py-2 bg-brand-blue text-white rounded-md hover:bg-brand-blue-light transition-colors"
        >
          {getTranslation('documentsPage.download', 'Изтегли файл')}
        </a>
      </div>
    );
  };

  const breadcrumbs = getBreadcrumbs();

  return (
    <PageWrapper title={getTranslation('documentsPage.title', 'Документи')}>
      <div className="space-y-8">
        <p className="text-lg text-gray-600">
          {getTranslation(
            'documentsPage.description',
            'Разгледайте и изтеглете всички налични документи на училището. Кликнете върху документ, за да го визуализирате.'
          )}
        </p>

        {/* Breadcrumb Navigation */}
        {currentPath && (
          <div className="bg-white border border-gray-200 rounded-lg p-4">
            <nav className="flex items-center space-x-2 text-sm">
              <button
                onClick={() => navigateToPath('')}
                className="text-brand-blue hover:text-brand-blue-light transition-colors font-medium"
              >
                📁 {getTranslation('documentsPage.home', 'Документи')}
              </button>
              {breadcrumbs.map((crumb, index) => (
                <React.Fragment key={crumb.path}>
                  <span className="text-gray-400">/</span>
                  {index === breadcrumbs.length - 1 ? (
                    <span className="text-gray-700 font-semibold">{crumb.name}</span>
                  ) : (
                    <button
                      onClick={() => navigateToPath(crumb.path)}
                      className="text-brand-blue hover:text-brand-blue-light transition-colors"
                    >
                      {crumb.name}
                    </button>
                  )}
                </React.Fragment>
              ))}
            </nav>
          </div>
        )}

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
              {/* Back button when in subfolder */}
              {currentPath && (
                <button
                  onClick={() => {
                    const parentPath = currentPath.split('/').slice(0, -1).join('/');
                    navigateToPath(parentPath);
                  }}
                  className="w-full text-left rounded-lg border border-gray-300 bg-gray-50 p-4 transition-colors hover:bg-gray-100 hover:border-gray-400"
                >
                  <div className="flex items-center gap-3">
                    <span className="text-2xl">⬅️</span>
                    <div className="flex-1">
                      <p className="font-semibold text-gray-700">
                        {getTranslation('documentsPage.back', '.. (Назад)')}
                      </p>
                    </div>
                  </div>
                </button>
              )}

              {documents.map((item) => {
                const isActive = selectedDocument?.filename === item.filename;
                const isFolder = item.isDirectory;

                return (
                  <button
                    key={item.filename}
                    onClick={() => handleSelectItem(item)}
                    className={`w-full text-left rounded-lg border p-4 transition-colors ${
                      isActive
                        ? 'border-brand-blue bg-brand-blue/10 text-brand-blue'
                        : 'border-gray-200 bg-white hover:border-brand-blue/40 hover:bg-brand-blue/5'
                    }`}
                  >
                    <div className="flex items-center gap-3">
                      <span className="text-2xl">
                        {isFolder ? '📁' : (isPdf(item.filename) ? '📄' : '📎')}
                      </span>
                      <div className="flex-1 min-w-0">
                        <p className="font-semibold truncate">{item.filename}</p>
                        {!isFolder && (
                          <div className="mt-1 flex items-center justify-between text-sm text-gray-500">
                            <span>{formatBytes(item.size)}</span>
                            {item.modified > 0 && (
                              <span>
                                {new Date(item.modified * 1000).toLocaleDateString()}
                              </span>
                            )}
                          </div>
                        )}
                        {isFolder && (
                          <p className="text-sm text-gray-500 mt-1">
                            {getTranslation('documentsPage.folder', 'Папка')}
                          </p>
                        )}
                      </div>
                      {isFolder && (
                        <span className="text-gray-400 text-xl">›</span>
                      )}
                    </div>
                  </button>
                );
              })}
            </div>

            <div className="lg:col-span-2 space-y-4">
              {selectedDocument && (
                <div className="flex items-center justify-between bg-white border border-gray-200 rounded-lg p-4">
                  <div className="flex-1 min-w-0">
                    <h2 className="text-xl font-semibold text-brand-blue">
                      {getTranslation('documentsPage.currentDocument', 'Избран документ')}
                    </h2>
                    <p className="text-gray-600 truncate">{selectedDocument.filename}</p>
                    {currentPath && (
                      <p className="text-sm text-gray-400 truncate">
                        {currentPath}/{selectedDocument.filename}
                      </p>
                    )}
                  </div>
                  <a
                    href={`${downloadBaseUrl}${encodeURIComponent(selectedDocument.path || selectedDocument.filename)}`}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="inline-flex items-center gap-2 px-4 py-2 bg-brand-blue text-white rounded-md hover:bg-brand-blue-light transition-colors whitespace-nowrap ml-4"
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
