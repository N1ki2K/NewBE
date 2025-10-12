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
  const { t, getTranslation } = useLanguage();
  const [navItems, setNavItems] = useState<NavItem[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [documentAutoItems, setDocumentAutoItems] = useState<NavItem[]>([]);

  // Fallback navigation for when API fails or is loading
  const getFallbackNavigation = (
    getTranslation: (key: string, fallback?: string) => string,
    documentChildren: NavItem[] = []
  ): NavItem[] => [
    { label: getTranslation('nav.home', 'Home'), path: '/' },
    {
      label: getTranslation('nav.school.title', 'School'),
      path: '/school',
      children: [
        { label: getTranslation('nav.school.history', 'History'), path: '/school/history' },
        { label: getTranslation('nav.school.patron', 'Patron'), path: '/school/patron' },
        { label: getTranslation('nav.school.team', 'Team'), path: '/school/team' },
        { label: getTranslation('nav.school.council', 'Council'), path: '/school/council' },
        { label: getTranslation('nav.school.news', 'News'), path: '/school/news' },
      ],
    },
    {
      label: getTranslation('nav.documents.title', 'Documents'),
      path: '/documents',
      children: documentChildren
    },
    { label: getTranslation('nav.gallery', 'Gallery'), path: '/gallery' },
    {
      label: getTranslation('nav.projects.title', 'Projects'),
      path: '/projects',
      children: []
    },
    { label: getTranslation('nav.contacts', 'Contacts'), path: '/contacts' },
    {
      label: getTranslation('nav.more', 'Още'),
      path: '/more',
      children: [
        { label: getTranslation('nav.usefulLinks', 'Useful Links'), path: '/useful-links' },
        { label: getTranslation('nav.infoAccess', 'Info Access'), path: '/info-access' },
      ]
    },
  ];

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

  const formatDocumentLabel = (filename: string): string => {
    const withoutExtension = filename.replace(/\.[^.]+$/, '');
    const cleaned = withoutExtension.replace(/[_-]+/g, ' ').replace(/\s+/g, ' ').trim();
    if (!cleaned) {
      return filename;
    }
    return cleaned
      .toLowerCase()
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ');
  };

  const buildDocumentLinks = (documents: any[] = []): NavItem[] => {
    return documents
      .filter(doc => doc && typeof doc.filename === 'string' && doc.filename.trim() !== '')
      .map(doc => {
        const filename = doc.filename.trim();
        return {
          label: doc.originalName
            ? doc.originalName
            : formatDocumentLabel(filename),
          path: `/documents/embed/${encodeURIComponent(filename)}`
        };
      });
  };

  const loadNavigation = async () => {
    try {
      setIsLoading(true);
      setError(null);
      
      // Load pages and dynamic navigation items
      const [pages, headerNav, documentsResponse] = await Promise.all([
        apiService.getPages(),
        apiService.getHeaderNavigation(),
        apiService.getDocuments().catch(() => ({ documents: [] }))
      ]);

      const autoDocumentLinks = buildDocumentLinks(documentsResponse?.documents);
      setDocumentAutoItems(autoDocumentLinks);
      
      // Build navigation structure combining pages with dynamic navigation items
      const buildNavigation = (pages: PageData[], dynamicNavItems: any[]): NavItem[] => {
        const navStructure: NavItem[] = [];
        
        // Define the desired order of top-level pages
        const pageOrder = ['home', 'school', 'documents', 'gallery', 'useful-links', 'projects', 'contacts', 'info-access'];
        
        // Process each page in the desired order
        pageOrder.forEach(pageId => {
          const page = pages.find(p => p.id === pageId && p.show_in_menu && (!p.parent_id || p.parent_id === null));
          if (page) {
            let navItem: NavItem;
            
            if (pageId === 'documents') {
              // Use dynamic navigation items for documents
              const documentsNav = dynamicNavItems.find(item => item.id === 'documents');
              const manualChildren = convertDynamicChildren(documentsNav?.children);
              const mergedChildren = [...manualChildren];

              autoDocumentLinks.forEach(link => {
                if (!mergedChildren.some(child => child.path === link.path)) {
                  mergedChildren.push(link);
                }
              });

              navItem = {
                label: getTranslatedLabel(page.id, page.name),
                path: page.path,
                children: mergedChildren
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
      setNavItems(navItems);
    } catch (err) {
      console.warn('Failed to load dynamic navigation, using fallback:', err);
      setError('Failed to load navigation');
      setNavItems(getFallbackNavigation(getTranslation, documentAutoItems));
    } finally {
      setIsLoading(false);
    }
  };

  const reloadNavigation = async () => {
    await loadNavigation();
  };

  useEffect(() => {
    loadNavigation();
  }, [t]); // Reload when language changes

  const finalNavItems = navItems.length > 0 ? navItems : getFallbackNavigation(getTranslation, documentAutoItems);
  
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
