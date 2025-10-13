import React, { useState, useEffect, useMemo } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useLanguage } from '../context/LanguageContext';
import { apiService } from '../src/services/api';
import { isBackendAvailable } from '../src/utils/backendChecker';
import { getArticleById } from '../src/data/hardcodedNews';
import { News, NewsArticleData } from '../components/News';

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

  const authorName =
    article.author && article.author.trim().length > 0
      ? article.author
      : language === 'bg'
        ? 'ОУ "Кольо Ганчев"'
        : 'Kolyo Ganchev Primary School';

  const downloadDescription =
    language === 'bg'
      ? 'Достъпете целия документ за допълнителна информация и ресурси.'
      : 'Access the complete document for additional details and resources.';

  const footerSuffix =
    language === 'bg' ? 'Всички права запазени.' : 'All rights reserved.';

  const featuredImageUrl =
    article.featuredImage ||
    (article as unknown as { featured_image_url?: string }).featured_image_url ||
    (article as unknown as { thumbnail_url?: string }).thumbnail_url ||
    undefined;

  const newsArticleData: NewsArticleData = {
    title: article.title,
    subtitle: article.excerpt,
    date: formattedDate,
    dateTime: article.publishedDate,
    author: authorName,
    image: featuredImageUrl,
    imageAlt:
      article.featuredImageAlt ||
      (article as unknown as { featured_image_alt?: string }).featured_image_alt ||
      article.title,
    content: hasHtmlContent ? [] : contentParagraphs,
    htmlContent: hasHtmlContent ? article.content : undefined,
    pdfUrl: pdfAttachmentInfo?.url,
    pdfFileName: pdfAttachmentInfo?.name,
    isFeatured: article.isFeatured,
  };

  const labels = {
    backToNews: getTranslation(
      'news.backToList',
      language === 'bg' ? 'Назад към новините' : 'Back to News',
    ),
    authorLabel: language === 'bg' ? 'Автор:' : 'Author:',
    featuredLabel: getTranslation('news.featured', 'Препоръчано'),
    downloadDocument: getTranslation(
      'news.downloadDocument',
      'Download document',
    ),
    downloadDescription,
    noImage: getTranslation(
      'news.noImage',
      language === 'bg' ? 'Няма изображение' : 'No image available',
    ),
    footer: `© ${new Date().getFullYear()} ОУ "Кольо Ганчев". ${footerSuffix}`,
  };

  return <News article={newsArticleData} onBack={handleBack} labels={labels} />;

};

export default NewsArticlePage;
