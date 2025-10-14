import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import { apiService } from '../src/services/api';
import { useLanguage } from './LanguageContext';

interface NavItem {
  label: string;
  path: string;
  children?: NavItem[];
}

interface PageData {
  id: string;
  name: string;
  path: string;
  parent_id?: string | null;
  position: number;
  is_active: boolean;
  show_in_menu: boolean;
  children?: PageData[];
}

interface NavigationContextType {
  navItems: NavItem[];
  isLoading: boolean;
  error: string | null;
  reloadNavigation: () => Promise<void>;
}

const NavigationContext = createContext<NavigationContextType | undefined>(undefined);

interface NavigationProviderProps {
  children: ReactNode;
}

export const NavigationProvider: React.FC<NavigationProviderProps> = ({ children }) => {
  const { t, getTranslation, language } = useLanguage();
  const [navItems, setNavItems] = useState<NavItem[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const humanizeFilename = (filename: string): string => {
    const withoutExtension = filename.replace(/\.[^/.]+$/, '');
    const withSpaces = withoutExtension.replace(/[_-]+/g, ' ').replace(/\s+/g, ' ').trim();
    if (!withSpaces) {
      return filename;
    }
    return withSpaces
      .split(' ')
      .map((word) => (word ? word.charAt(0).toUpperCase() + word.slice(1) : word))
      .join(' ');
  };

  const buildDocumentNavChildren = (entries: Array<{ filename: string; title?: string }>): NavItem[] => {
    const seen = new Set<string>();
    const docs = entries
      .map((entry) => {
        const { filename, title } = entry;
        if (!filename) {
          return null;
        }
        if (seen.has(filename)) {
          return null;
        }
        seen.add(filename);
        const label = title && title.trim().length > 0 ? title : humanizeFilename(filename);
        return {
          label,
          path: `/documents?file=${encodeURIComponent(filename)}`,
        };
      })
      .filter((item): item is NavItem => Boolean(item));

    return docs.sort((a, b) =>
      a.label.localeCompare(b.label, language === 'bg' ? 'bg' : 'en', { sensitivity: 'base' })
    );
  };

  const loadDocumentNavChildren = async (): Promise<NavItem[]> => {
    try {
      const response = await apiService.getDocuments();
      const fromApi = buildDocumentNavChildren(response.documents || []);
      return fromApi;
    } catch (docError) {
      console.warn('Failed to load documents for navigation:', docError);
      return [];
    }
  };

  const normalizePath = (path: string): string => {
    if (!path) return '';
    const trimmed = path.replace(/\/+$/, '');
    return trimmed === '' ? '/' : trimmed;
  };

  const withDocumentChildren = (items: NavItem[], documentChildren: NavItem[]): NavItem[] => {
    if (documentChildren.length === 0) {
      return items;
    }
    return items.map((item) => {
      if (normalizePath(item.path) === '/documents') {
        return {
          ...item,
          children: documentChildren,
        };
      }
      return item;
    });
  };

  // Fallback navigation for when API fails or is loading
  const getFallbackNavigation = (
    translate: (key: string, fallback?: string) => string,
    documentChildren: NavItem[]
  ): NavItem[] =>
    withDocumentChildren(
      [
        { label: translate('nav.home', 'Home'), path: '/' },
        {
          label: translate('nav.school.title', 'School'),
          path: '/school',
          children: [
            { label: translate('nav.school.history', 'History'), path: '/school/history' },
            { label: translate('nav.school.patron', 'Patron'), path: '/school/patron' },
            { label: translate('nav.school.team', 'Team'), path: '/school/team' },
            { label: translate('nav.school.council', 'Council'), path: '/school/council' },
            { label: translate('nav.school.news', 'News'), path: '/school/news' },
          ],
        },
        {
          label: translate('nav.documents.title', 'Documents'),
          path: '/documents',
          children: documentChildren,
        },
        { label: translate('nav.gallery', 'Gallery'), path: '/gallery' },
        {
          label: translate('nav.projects.title', 'Projects'),
          path: '/projects',
          children: [],
        },
        { label: translate('nav.contacts', 'Contacts'), path: '/contacts' },
        {
          label: translate('nav.more', 'Още'),
          path: '/more',
          children: [
            { label: translate('nav.usefulLinks', 'Useful Links'), path: '/useful-links' },
            { label: translate('nav.infoAccess', 'Info Access'), path: '/info-access' },
          ],
        },
      ],
      documentChildren
    );

  const getTranslatedLabel = (pageId: string, pageName: string): string => {
    const labelMap: Record<string, string> = {
      'home': getTranslation('nav.home', 'Home'),
      'school': getTranslation('nav.school.title', 'School'),
      'school-history': getTranslation('nav.school.history', 'History'),
      'school-patron': getTranslation('nav.school.patron', 'Patron'),
      'school-team': getTranslation('nav.school.team', 'Team'),
      'school-council': getTranslation('nav.school.council', 'Council'),
      'school-news': getTranslation('nav.school.news', 'News'),
      'documents': getTranslation('nav.documents.title', 'Documents'),
      'documents-calendar': getTranslation('nav.documents.calendar', 'Calendar'),
      'documents-schedules': getTranslation('nav.documents.schedules', 'Schedules'),
      'documents-budget': getTranslation('nav.documents.budget', 'Budget Reports'),
      'documents-rules': getTranslation('nav.documents.rules', 'Rules'),
      'documents-ethics': getTranslation('nav.documents.ethics', 'Ethics Code'),
      'documents-admin-services': getTranslation('nav.documents.adminServices', 'Admin Services'),
      'documents-admissions': getTranslation('nav.documents.admissions', 'Admissions'),
      'documents-road-safety': getTranslation('nav.documents.roadSafety', 'Road Safety'),
      'documents-ores': getTranslation('nav.documents.ores', 'ORES'),
      'documents-continuing-education': getTranslation('nav.documents.continuingEducation', 'Continuing Education'),
      'documents-faq': getTranslation('nav.documents.faq', 'FAQ'),
      'documents-announcement': getTranslation('nav.documents.announcement', 'Announcements'),
      'documents-students': getTranslation('nav.documents.students', 'Students'),
      'documents-olympiads': getTranslation('nav.documents.olympiads', 'Olympiads'),
      'projects': getTranslation('nav.projects.title', 'Projects'),
      'projects-your-hour': getTranslation('nav.projects.yourHour', 'Project "Your Hour"'),
      'projects-support-success': getTranslation('nav.projects.supportForSuccess', 'Project "Support for Success"'),
      'projects-education-tomorrow': getTranslation('nav.projects.educationForTomorrow', 'Project "Education for Tomorrow"'),
      'useful-links': getTranslation('nav.usefulLinks', 'Useful Links'),
      'gallery': getTranslation('nav.gallery', 'Gallery'),
      'contacts': getTranslation('nav.contacts', 'Contacts'),
      'info-access': getTranslation('nav.infoAccess', 'Info Access')
    };
    
    return labelMap[pageId] || pageName;
  };

  const transformPageToNavItem = (page: PageData): NavItem => {
    return {
      label: getTranslatedLabel(page.id, page.name),
      path: page.path,
      children: page.children ? page.children.map(transformPageToNavItem) : undefined,
    };
  };

  const normalizeDynamicItem = (item: any): NavItem => {
    const label =
      typeof item?.label === 'string' && item.label.trim().length > 0
        ? item.label.trim()
        : typeof item?.title === 'string' && item.title.trim().length > 0
          ? item.title.trim()
          : getTranslation('nav.untitled', 'Untitled');

    const path =
      typeof item?.path === 'string' && item.path.trim().length > 0
        ? item.path
        : '#';

    const children = Array.isArray(item?.children)
      ? item.children
          .filter(Boolean)
          .map(normalizeDynamicItem)
          .filter((child) => child.label && child.path)
      : [];

    return {
      label,
      path,
      ...(children.length > 0 ? { children } : {})
    };
  };

  const convertDynamicChildren = (items: any[] | undefined): NavItem[] => {
    if (!Array.isArray(items)) {
      return [];
    }
    return items
      .filter(Boolean)
      .map(normalizeDynamicItem)
      .filter((item) => item.label && item.path);
  };

  const loadNavigation = async () => {
    setIsLoading(true);
    setError(null);

    const documentChildren = await loadDocumentNavChildren();

    try {

      // Load pages and dynamic navigation items
      const [pages, headerNav] = await Promise.all([
        apiService.getPages(),
        apiService.getHeaderNavigation()
      ]);
      
      // Build navigation structure combining pages with dynamic navigation items
      const buildNavigation = (pages: PageData[], dynamicNavItems: any[]): NavItem[] => {
        const navStructure: NavItem[] = [];
        
        // Define the desired order of top-level pages
        const pageOrder = ['home', 'school', 'documents', 'gallery', 'useful-links', 'projects', 'contacts', 'info-access'];
        
        // Process each page in the desired order
        pageOrder.forEach((pageId) => {
          const page = pages.find(p => p.id === pageId && p.show_in_menu && (!p.parent_id || p.parent_id === null));
          if (page) {
            let navItem: NavItem;
            
            if (pageId === 'documents') {
              // Use dynamic navigation items for documents
              const documentsNav = dynamicNavItems.find(item => item.id === 'documents');
              navItem = {
                label: getTranslatedLabel(page.id, page.name),
                path: page.path,
                children:
                  (documentChildren.length > 0
                    ? documentChildren
                    : convertDynamicChildren(documentsNav?.children))
              };
            } else if (pageId === 'projects') {
              // Use dynamic navigation items for projects
              const projectsNav = dynamicNavItems.find(item => item.id === 'projects');
              navItem = {
                label: getTranslatedLabel(page.id, page.name),
                path: page.path,
                children: convertDynamicChildren(projectsNav?.children)
              };
            } else {
              // Use the already-structured children from backend
              navItem = transformPageToNavItem(page);
              
              // If this is school, make sure children are properly handled
              if (pageId === 'school' && page.children && page.children.length > 0) {
                navItem.children = page.children.map(transformPageToNavItem);
              }
            }
            
            navStructure.push(navItem);
          }
        });
        
        return navStructure;
      };
      const navItems = buildNavigation(pages, headerNav.navigation);
      setNavItems(withDocumentChildren(navItems, documentChildren));
    } catch (err) {
      console.warn('Failed to load dynamic navigation, using fallback:', err);
      setError('Failed to load navigation');
      setNavItems(getFallbackNavigation(getTranslation, documentChildren));
    } finally {
      setIsLoading(false);
    }
  };

  const reloadNavigation = async () => {
    await loadNavigation();
  };

  useEffect(() => {
    loadNavigation();
  }, [t, language]); // Reload when language changes

  const finalNavItems =
    navItems.length > 0 ? navItems : getFallbackNavigation(getTranslation, []);
  
  // Debug logging
  console.log('🧭 Navigation Debug:', {
    navItemsFromDB: navItems.length,
    finalNavItems: finalNavItems.length,
    samplePaths: finalNavItems.slice(0, 3).map(item => ({ label: item.label, path: item.path }))
  });

  const contextValue: NavigationContextType = {
    navItems: finalNavItems,
    isLoading,
    error,
    reloadNavigation,
  };

  return (
    <NavigationContext.Provider value={contextValue}>
      {children}
    </NavigationContext.Provider>
  );
};

export const useNavigationContext = (): NavigationContextType => {
  const context = useContext(NavigationContext);
  if (context === undefined) {
    throw new Error('useNavigationContext must be used within a NavigationProvider');
  }
  return context;
};
