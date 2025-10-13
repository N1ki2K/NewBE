import React, { useState, useEffect, useMemo } from 'react';
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
  author?: string;
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

interface IconProps {
  className?: string;
}

const ArrowLeftIcon: React.FC<IconProps> = ({ className = 'w-4 h-4' }) => (
  <svg className={className} fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
  </svg>
);

const CalendarIcon: React.FC<IconProps> = ({ className = 'w-4 h-4' }) => (
  <svg className={className} fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 7V3m8 4V3m-9 8h10m-12 8h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
  </svg>
);

const FileTextIcon: React.FC<IconProps> = ({ className = 'w-4 h-4' }) => (
  <svg className={className} fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
  </svg>
);

const NewsArticlePage: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const { t, getTranslation, language } = useLanguage();
  const [article, setArticle] = useState<NewsArticle | null>(null);
  const [attachments, setAttachments] = useState<NewsAttachment[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const pdfAttachmentInfo = useMemo(() => {
    const pdfFromAttachments = attachments.find((attachment) => {
      const name = (attachment.original_name || attachment.filename || '').toLowerCase();
      const mime = (attachment.mime_type || '').toLowerCase();
      return name.endsWith('.pdf') || mime.includes('pdf');
    });

    if (pdfFromAttachments) {
      return {
        url: pdfFromAttachments.file_url,
        name: pdfFromAttachments.original_name || pdfFromAttachments.filename,
      };
    }

    if (article?.attachment_url) {
      return {
        url: article.attachment_url,
        name: article.attachment_name || article.title,
      };
    }

    return null;
  }, [attachments, article]);

  const formattedDate = useMemo(() => {
    if (!article?.publishedDate) return '';
    try {
      return new Date(article.publishedDate).toLocaleDateString(
        language === 'bg' ? 'bg-BG' : 'en-US',
        {
          year: 'numeric',
          month: 'long',
          day: 'numeric',
        }
      );
    } catch (dateError) {
      console.error('Failed to format article date:', dateError);
      return article.publishedDate;
    }
  }, [article?.publishedDate, language]);

  const hasHtmlContent = useMemo(() => {
    if (!article?.content) return false;
    return /<[a-z][\s\S]*>/i.test(article.content);
  }, [article?.content]);

  const contentParagraphs = useMemo(() => {
    if (!article?.content || hasHtmlContent) {
      return [] as string[];
    }
    return article.content
      .split(/\n\s*\n/)
      .map((paragraph) => paragraph.trim())
      .filter((paragraph) => paragraph.length > 0);
  }, [article?.content, hasHtmlContent]);

  const handleBack = () => {
    if (window.history.length > 1) {
      navigate(-1);
    } else {
      navigate('/news');
    }
  };

  const handlePdfDownload = () => {
    if (pdfAttachmentInfo?.url) {
      window.open(pdfAttachmentInfo.url, '_blank');
    }
  };
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

  if (isLoading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-brand-blue" />
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
          <p className="text-gray-600 mb-6">{getTranslation('news.notFoundMessage', 'The requested article was not found.')}</p>
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

  const authorName = (article.author && article.author.trim().length > 0)
    ? article.author
    : (language === 'bg' ? 'ОУ "Кольо Ганчев"' : 'Kolyo Ganchev Primary School');

  const downloadDescription = language === 'bg'
    ? 'Достъпете целия документ за допълнителна информация и ресурси.'
    : 'Access the complete document for additional details and resources.';

  const footerText = language === 'bg'
    ? 'Всички права запазени.'
    : 'All rights reserved.';

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col">
      <header className="bg-white/90 border-b backdrop-blur sticky top-0 z-10">
        <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center">
          <button
            onClick={handleBack}
            className="inline-flex items-center gap-2 text-brand-blue hover:text-brand-blue-dark transition-colors"
          >
            <ArrowLeftIcon className="w-4 h-4" />
            {getTranslation('news.backToList', language === 'bg' ? 'Назад към новините' : 'Back to News')}
          </button>
        </div>
      </header>

      <article className="max-w-4xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-10 flex-1">
        <div className="relative w-full aspect-video rounded-lg overflow-hidden bg-gray-200">
          {article.featuredImage ? (
            <img
              src={article.featuredImage}
              alt={article.featuredImageAlt || article.title}
              className="w-full h-full object-cover"
            />
          ) : (
            <div className="w-full h-full flex items-center justify-center text-gray-500">
              <span className="text-sm">
                {getTranslation('news.noImage', language === 'bg' ? 'Няма изображение' : 'No image available')}
              </span>
            </div>
          )}
        </div>

        <div className="space-y-4 mt-8 mb-8">
          <h1 className="text-3xl sm:text-4xl font-bold text-brand-blue leading-tight">
            {article.title}
          </h1>

          {article.excerpt && (
            <h2 className="text-lg sm:text-xl text-gray-600">
              {article.excerpt}
            </h2>
          )}

          <div className="flex items-center gap-4 text-sm text-gray-500 flex-wrap">
            <div className="flex items-center gap-2">
              <CalendarIcon className="w-4 h-4" />
              <time dateTime={article.publishedDate}>{formattedDate}</time>
            </div>
            <span className="text-gray-400">•</span>
            <span>
              {language === 'bg' ? 'Автор:' : 'Author:'} {authorName}
            </span>
            {article.isFeatured && (
              <span className="inline-flex items-center gap-2 rounded-full bg-brand-gold text-brand-blue-dark px-3 py-1 text-xs font-semibold">
                {getTranslation('news.featured', 'Препоръчано')}
              </span>
            )}
          </div>
        </div>

        <div className="h-px w-full bg-gray-200 mb-8" />

        <div className="prose prose-lg max-w-none space-y-6 text-gray-800 mb-8">
          {hasHtmlContent && article.content ? (
            <div dangerouslySetInnerHTML={{ __html: article.content }} />
          ) : (
            contentParagraphs.map((paragraph, index) => (
              <p key={index} className="leading-relaxed">
                {paragraph}
              </p>
            ))
          )}
        </div>

        {pdfAttachmentInfo && (
          <div className="space-y-6">
            <div className="h-px w-full bg-gray-200" />
            <div className="bg-brand-blue/5 border border-brand-blue/20 rounded-lg p-6">
              <div className="flex items-start gap-4">
                <div className="p-3 bg-brand-blue text-white rounded-lg">
                  <FileTextIcon className="w-6 h-6" />
                </div>
                <div className="flex-1 space-y-3">
                  <h3 className="text-lg font-semibold text-brand-blue">
                    {getTranslation('news.downloadDocument', 'Download document')}
                  </h3>
                  <p className="text-gray-600">
                    {downloadDescription}
                  </p>
                  <button
                    onClick={handlePdfDownload}
                    className="inline-flex items-center gap-2 px-4 py-2 bg-brand-blue text-white rounded-md hover:bg-brand-blue-dark transition-colors"
                  >
                    <FileTextIcon className="w-4 h-4" />
                    {getTranslation('news.downloadDocument', 'Download document')} {pdfAttachmentInfo.name}
                  </button>
                </div>
              </div>
            </div>
          </div>
        )}
      </article>

      <footer className="bg-brand-blue/5 border-t border-brand-blue/10">
        <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-center text-gray-500">
          © {new Date().getFullYear()} ОУ "Кольо Ганчев". {footerText}
        </div>
      </footer>
    </div>
  );

};

export default NewsArticlePage;
