import React, { useState, useEffect } from 'react';
import PageWrapper from '../components/PageWrapper';
import { useLanguage } from '../context/LanguageContext';
import { apiService } from '../src/services/api';
import { mockUsefulLinks } from '../src/data/mockUsefulLinks';

interface UsefulLink {
  id: number;
  link_key: string;
  title: string;
  description?: string;
  url: string;
  cta?: string;
  position: number;
}

interface UsefulLinksContent {
  id: number;
  section_key: string;
  title?: string;
  content?: string;
  position: number;
}

const LinkCard: React.FC<{ 
  title: string; 
  url: string; 
  description?: string; 
  cta?: string;
  defaultCta: string;
}> = ({ title, url, description, cta, defaultCta }) => (
    <a href={url} target="_blank" rel="noopener noreferrer" className="block p-6 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-lg hover:border-brand-gold transition-all duration-300 group">
        <h3 className="text-xl font-bold text-brand-blue mb-2">{title}</h3>
        {description && (
          <p className="text-gray-600 mb-3">{description}</p>
        )}
        <span className="text-brand-blue-light font-semibold group-hover:text-brand-gold transition-colors">
          {cta || defaultCta} &rarr;
        </span>
    </a>
)

const normalizeLink = (link: any, index: number, locale: string): UsefulLink => {
  const titleCandidates = [
    typeof link?.title === 'string' ? link.title : null,
    locale === 'en' ? link?.title_en : link?.title_bg,
    locale === 'bg' ? link?.title_en : link?.title_bg,
  ];

  const normalizedTitle =
    titleCandidates
      .map((value) => (typeof value === 'string' ? value.trim() : ''))
      .find((value) => value.length > 0) || 'Untitled link';

  const normalizedDescription =
    (typeof link?.description === 'string' && link.description.trim().length > 0
      ? link.description
      : locale === 'en'
        ? link?.description_en
        : link?.description_bg) || undefined;

  const normalizedCta =
    (typeof link?.cta === 'string' && link.cta.trim().length > 0
      ? link.cta
      : locale === 'en'
        ? link?.cta_en
        : link?.cta_bg) || undefined;

  const url = typeof link?.url === 'string' && link.url.trim().length > 0 ? link.url : '#';

  return {
    id: typeof link?.id === 'number' ? link.id : Number(link?.id) || index + 1,
    link_key:
      typeof link?.link_key === 'string' && link.link_key.trim().length > 0
        ? link.link_key
        : `link-${index + 1}`,
    title: normalizedTitle || 'Untitled link',
    description: normalizedDescription,
    url,
    cta: normalizedCta,
    position: typeof link?.position === 'number' ? link.position : index + 1,
  };
};

const normalizeContent = (item: any, index: number, locale: string): UsefulLinksContent => ({
  id: typeof item?.id === 'number' ? item.id : Number(item?.id) || index + 1,
  section_key:
    typeof item?.section_key === 'string' && item.section_key.trim().length > 0
      ? item.section_key
      : `section-${index + 1}`,
  title:
    (typeof item?.title === 'string' && item.title.trim().length > 0
      ? item.title
      : locale === 'en'
        ? item?.title_en
        : item?.title_bg) || undefined,
  content:
    (typeof item?.content === 'string' && item.content.trim().length > 0
      ? item.content
      : locale === 'en'
        ? item?.content_en
        : item?.content_bg) || undefined,
  position: typeof item?.position === 'number' ? item.position : index + 1,
});

const UsefulLinksPage: React.FC = () => {
  const { t, getTranslation, locale } = useLanguage();
  const [links, setLinks] = useState<UsefulLink[]>([]);
  const [content, setContent] = useState<UsefulLinksContent[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isFallbackData, setIsFallbackData] = useState(false);

  useEffect(() => {
    loadUsefulLinksContent();
  }, [locale]);

  const buildFallbackData = (): { links: UsefulLink[]; content: UsefulLinksContent[] } => {
    const fallbackLinks = mockUsefulLinks.map((item, index) => ({
      id: index + 1,
      link_key: item.key,
      title: item.title[locale] ?? item.title.bg,
      description: item.description?.[locale] ?? item.description?.bg,
      url: item.url,
      cta: item.cta?.[locale] ?? item.cta?.bg,
      position: index + 1,
    }));

    const fallbackContent: UsefulLinksContent[] = [
      {
        id: 1,
        section_key: 'intro',
        title: undefined,
        content: getTranslation('usefulLinksPage.intro', ''),
        position: 1,
      },
    ];

    return { links: fallbackLinks, content: fallbackContent };
  };

  const loadUsefulLinksContent = async () => {
    try {
      setIsLoading(true);
      const response = await apiService.getUsefulLinksContent(locale);
      const apiLinks = Array.isArray(response.links)
        ? response.links.map((link, index) => normalizeLink(link, index, locale))
        : [];
      const apiContent = Array.isArray(response.content)
        ? response.content.map((item, index) => normalizeContent(item, index, locale))
        : [];

      if (apiLinks.length === 0) {
        const fallback = buildFallbackData();
        setLinks(fallback.links);
        setContent(apiContent.length > 0 ? apiContent : fallback.content);
        setIsFallbackData(true);
      } else {
        setLinks(apiLinks);
        setContent(apiContent.length > 0 ? apiContent : buildFallbackData().content);
        setIsFallbackData(false);
      }
    } catch (err) {
      console.error('Failed to load useful links content:', err);
      const fallback = buildFallbackData();
      setLinks(fallback.links);
      setContent(fallback.content);
      setIsFallbackData(true);
    } finally {
      setIsLoading(false);
    }
  };

  const getContentByKey = (key: string) => {
    return content.find(item => item.section_key === key);
  };

  if (isLoading) {
    return (
      <PageWrapper title={getTranslation('usefulLinksPage.loadingTitle', 'Зареждане...')}>
        <div className="flex items-center justify-center p-8">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <span className="ml-3 text-gray-600">{getTranslation('usefulLinksPage.loading', 'Зарежда...')}</span>
        </div>
      </PageWrapper>
    );
  }

  const introContent = getContentByKey('intro');

  return (
    <PageWrapper title={getTranslation('usefulLinksPage.title', 'Полезни връзки')}>
      {isFallbackData && (
        <div className="mb-6 rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800">
          {getTranslation(
            'usefulLinksPage.fallbackNotice',
            locale === 'bg'
              ? 'Показваме предварително заредени полезни връзки, защото връзката със сървъра беше недостъпна.'
              : 'Showing preloaded useful links because the server connection was unavailable.'
          )}
        </div>
      )}
      {introContent && (
        <p className="mb-12 text-lg leading-relaxed text-gray-700">
          {introContent.content}
        </p>
      )}
      
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {links.map(link => (
          <LinkCard 
            key={link.id} 
            title={link.title}
            url={link.url}
            description={link.description}
            cta={link.cta}
            defaultCta={getTranslation('usefulLinksPage.defaultCta', 'Прочети повече')}
          />
        ))}
      </div>

      {links.length === 0 && (
        <div className="text-center py-8 text-gray-500">
          <p>{getTranslation('usefulLinksPage.noLinks', 'Няма връзки')}</p>
          <button
            onClick={loadUsefulLinksContent}
            className="mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
          >
            {getTranslation('usefulLinksPage.refresh', 'Опресни')}
          </button>
        </div>
      )}
    </PageWrapper>
  );
};

export default UsefulLinksPage;
