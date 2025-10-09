import React, { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useLanguage } from '../context/LanguageContext';
import { apiService } from '../src/services/api';
import { isBackendAvailable } from '../src/utils/backendChecker';
import { getArticleById } from '../src/data/hardcodedNews';

interface NewsArticle {
  id: string;
  title: string;
  excerpt: string;
  content: string;
  featuredImage?: string;
  featuredImageAlt?: string;
  publishedDate: string;
  isPublished: boolean;
  isFeatured: boolean;
  attachment_url?: string;
  attachment_name?: string;
}

interface NewsAttachment {
  id: string;
  news_id: string;
  filename: string;
  original_name: string;
  file_url: string;
  file_size: number;
  mime_type: string;
  created_at: string;
}

const NewsArticlePage: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const { t, getTranslation, language } = useLanguage();
  const [article, setArticle] = useState<NewsArticle | null>(null);
  const [attachments, setAttachments] = useState<NewsAttachment[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [viewerUrl, setViewerUrl] = useState<string | null>(null);

  useEffect(() => {
    const loadArticle = async () => {
      if (!id) {
        setError('No article ID provided');
        setIsLoading(false);
        return;
      }

      try {
        setIsLoading(true);

        // Check if backend is available
        const backendOnline = await isBackendAvailable();

        if (backendOnline) {
          console.log('[NewsArticlePage] Backend is online, loading from API...');
          const articleData = await apiService.getNewsArticle(id, language);
          setArticle(articleData);

          // Fetch attachments
          try {
            const attachmentsData = await apiService.getNewsAttachments(id);
            setAttachments(attachmentsData);
          } catch (attachError) {
            console.error('Error loading attachments:', attachError);
            // Don't fail the whole page if attachments fail to load
            setAttachments([]);
          }

          setError(null);
        } else {
          console.log('[NewsArticlePage] Backend is offline, loading hardcoded data...');
          // Load from hardcoded TypeScript file
          const hardcodedArticle = getArticleById(id, language);

          if (hardcodedArticle) {
            setArticle({
              id: hardcodedArticle.id,
              title: hardcodedArticle.title,
              excerpt: hardcodedArticle.excerpt,
              content: hardcodedArticle.content,
              featuredImage: hardcodedArticle.featured_image_url,
              featuredImageAlt: hardcodedArticle.title,
              publishedDate: hardcodedArticle.published_date,
              isPublished: hardcodedArticle.is_published,
              isFeatured: hardcodedArticle.is_featured,
              attachment_url: hardcodedArticle.attachment_url,
              attachment_name: hardcodedArticle.attachment_name
            });

            // If hardcoded data has attachment, create a pseudo-attachment object
            if (hardcodedArticle.attachment_url && hardcodedArticle.attachment_name) {
              setAttachments([{
                id: 'hardcoded-attachment',
                news_id: id,
                filename: hardcodedArticle.attachment_name,
                original_name: hardcodedArticle.attachment_name,
                file_url: hardcodedArticle.attachment_url,
                file_size: 0,
                mime_type: hardcodedArticle.attachment_name.endsWith('.pdf') ? 'application/pdf' :
                           hardcodedArticle.attachment_name.endsWith('.docx') ? 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' : '',
                created_at: new Date().toISOString()
              }]);
            }

            setError(null);
          } else {
            setError('Article not found');
          }
        }
      } catch (error) {
        console.error('[NewsArticlePage] Error loading news article, using hardcoded fallback:', error);
        // Try to get data from hardcoded fallback
        try {
          const hardcodedArticle = getArticleById(id, language);

          if (hardcodedArticle) {
            setArticle({
              id: hardcodedArticle.id,
              title: hardcodedArticle.title,
              excerpt: hardcodedArticle.excerpt,
              content: hardcodedArticle.content,
              featuredImage: hardcodedArticle.featured_image_url,
              featuredImageAlt: hardcodedArticle.title,
              publishedDate: hardcodedArticle.published_date,
              isPublished: hardcodedArticle.is_published,
              isFeatured: hardcodedArticle.is_featured,
              attachment_url: hardcodedArticle.attachment_url,
              attachment_name: hardcodedArticle.attachment_name
            });

            // If hardcoded data has attachment, create a pseudo-attachment object
            if (hardcodedArticle.attachment_url && hardcodedArticle.attachment_name) {
              setAttachments([{
                id: 'hardcoded-attachment',
                news_id: id,
                filename: hardcodedArticle.attachment_name,
                original_name: hardcodedArticle.attachment_name,
                file_url: hardcodedArticle.attachment_url,
                file_size: 0,
                mime_type: hardcodedArticle.attachment_name.endsWith('.pdf') ? 'application/pdf' :
                           hardcodedArticle.attachment_name.endsWith('.docx') ? 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' : '',
                created_at: new Date().toISOString()
              }]);
            }

            setError(null);
          } else {
            setError('Article not found');
          }
        } catch (fallbackError) {
          console.error('[NewsArticlePage] Hardcoded fallback also failed:', fallbackError);
          setError('Failed to load article');
        }
      } finally {
        setIsLoading(false);
      }
    };

    loadArticle();
  }, [id, language]);

  const handleViewDocument = (url: string) => {
    setViewerUrl(url);
  };

  const closeViewer = () => {
    setViewerUrl(null);
  };

  if (isLoading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-brand-blue"></div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <h1 className="text-2xl font-bold text-gray-900 mb-4">{getTranslation('common.error', 'Грешка')}</h1>
          <p className="text-gray-600 mb-6">{error}</p>
          <button
            onClick={() => navigate('/')}
            className="bg-brand-blue text-white px-6 py-2 rounded-lg hover:bg-brand-blue-dark transition-colors"
          >
            {getTranslation('common.goHome', 'Начало')}
          </button>
        </div>
      </div>
    );
  }

  if (!article) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <h1 className="text-2xl font-bold text-gray-900 mb-4">{getTranslation('common.notFound', 'Не е намерено')}</h1>
          <p className="text-gray-600 mb-6">The requested article was not found.</p>
          <button
            onClick={() => navigate('/')}
            className="bg-brand-blue text-white px-6 py-2 rounded-lg hover:bg-brand-blue-dark transition-colors"
          >
            {getTranslation('common.goHome', 'Начало')}
          </button>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {/* Back button */}
        <button
          onClick={() => navigate('/')}
          className="inline-flex items-center text-brand-blue hover:text-brand-blue-dark transition-colors mb-6"
        >
          <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
          </svg>
          {getTranslation('common.back', 'Назад')}
        </button>

        {/* Article content */}
        <article className="bg-white rounded-lg shadow-lg overflow-hidden max-w-4xl mx-auto">
          {/* Featured image */}
          {article.featuredImage && (
            <div className="w-full h-64 sm:h-80 md:h-96">
              <img
                src={article.featuredImage}
                alt={article.featuredImageAlt || article.title}
                className="w-full h-full object-cover"
              />
            </div>
          )}

          <div className="p-6 sm:p-8 md:p-12">
            {/* Article header */}
            <header className="mb-8">
              <div className="flex items-center text-sm text-gray-500 mb-4">
                <time dateTime={article.publishedDate}>
                  {new Date(article.publishedDate).toLocaleDateString(
                    language === 'bg' ? 'bg-BG' : 'en-US',
                    {
                      year: 'numeric',
                      month: 'long',
                      day: 'numeric'
                    }
                  )}
                </time>
                {article.isFeatured && (
                  <>
                    <span className="mx-2">•</span>
                    <span className="bg-brand-gold text-brand-blue-dark px-2 py-1 rounded text-xs font-semibold">
                      {getTranslation('news.featured', 'Препоръчано')}
                    </span>
                  </>
                )}
              </div>
              
              <h1 className="text-3xl sm:text-4xl md:text-5xl font-bold text-brand-blue leading-tight mb-6">
                {article.title}
              </h1>
              
              {article.excerpt && (
                <p className="text-lg sm:text-xl text-gray-600 leading-relaxed">
                  {article.excerpt}
                </p>
              )}
            </header>

            {/* Article content */}
            <div className="prose prose-lg max-w-none">
              <div
                className="text-gray-800 leading-relaxed"
                dangerouslySetInnerHTML={{
                  __html: article.content?.replace(/\n/g, '<br>') || ''
                }}
              />
            </div>

            {/* Attachments section */}
            {attachments.length > 0 && (
              <div className="mt-8 pt-8 border-t border-gray-200">
                <h2 className="text-xl font-semibold text-brand-blue mb-4">
                  {getTranslation('news.attachments', 'Прикачени документи')}
                </h2>
                <div className="space-y-3">
                  {attachments.map((attachment) => (
                    <div
                      key={attachment.id}
                      className="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors group"
                    >
                      <svg
                        className="w-8 h-8 text-brand-blue mr-3 flex-shrink-0"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          strokeLinecap="round"
                          strokeLinejoin="round"
                          strokeWidth={2}
                          d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                        />
                      </svg>
                      <div className="flex-1 min-w-0">
                        <p className="text-sm font-medium text-gray-900 group-hover:text-brand-blue transition-colors truncate">
                          {attachment.original_name}
                        </p>
                        <p className="text-xs text-gray-500">
                          {(attachment.file_size / 1024).toFixed(1)} KB
                        </p>
                      </div>
                      <div className="flex items-center gap-2">
                        <button
                          onClick={() => handleViewDocument(attachment.file_url)}
                          className="p-2 text-brand-blue hover:bg-brand-blue hover:text-white rounded transition-colors"
                          title={getTranslation('news.viewDocument', 'Преглед')}
                        >
                          <svg
                            className="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                          >
                            <path
                              strokeLinecap="round"
                              strokeLinejoin="round"
                              strokeWidth={2}
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                            />
                            <path
                              strokeLinecap="round"
                              strokeLinejoin="round"
                              strokeWidth={2}
                              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                            />
                          </svg>
                        </button>
                        <a
                          href={attachment.file_url}
                          download
                          className="p-2 text-brand-blue hover:bg-brand-blue hover:text-white rounded transition-colors"
                          title={getTranslation('news.downloadDocument', 'Изтегляне')}
                        >
                          <svg
                            className="w-5 h-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                          >
                            <path
                              strokeLinecap="round"
                              strokeLinejoin="round"
                              strokeWidth={2}
                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                            />
                          </svg>
                        </a>
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            )}
          </div>
        </article>

        {/* Navigation */}
        <div className="max-w-4xl mx-auto mt-8 flex justify-center">
          <button
            onClick={() => navigate('/')}
            className="bg-brand-blue text-white px-8 py-3 rounded-lg hover:bg-brand-blue-dark transition-colors font-semibold"
          >
            {getTranslation('common.backToHome', 'Към началото')}
          </button>
        </div>
      </div>

      {/* Document Viewer Modal */}
      {viewerUrl && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75">
          <div className="relative w-full h-full max-w-7xl max-h-[90vh] m-4 bg-white rounded-lg shadow-xl flex flex-col">
            <div className="flex items-center justify-between p-4 border-b">
              <h3 className="text-lg font-semibold text-gray-900">
                {getTranslation('news.documentPreview', 'Преглед на документ')}
              </h3>
              <button
                onClick={closeViewer}
                className="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded transition-colors"
              >
                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <div className="flex-1 overflow-hidden">
              <iframe
                src={viewerUrl}
                className="w-full h-full border-0"
                title="Document Viewer"
              />
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default NewsArticlePage;