import React from 'react';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { LanguageProvider } from './context/LanguageContext';
import { CMSProvider } from './context/CMSContext';
import { NavigationProvider } from './context/NavigationContext';
import Layout from './components/Layout';
import ErrorBoundary from './components/ErrorBoundary';

// Import Page Components
import HomePage from './pages/HomePage';
import HistoryPage from './pages/school/HistoryPage';
import PatronPage from './pages/school/PatronPage';
import TeamPage from './pages/school/TeamPage';
import CouncilPage from './pages/school/CouncilPage';
import SchoolNewsPage from './pages/school/SchoolNewsPage';
import UsefulLinksPage from './pages/UsefulLinksPage';
import GalleryPage from './pages/GalleryPage';
import ContactsPage from './pages/ContactsPage';
import InfoAccessPage from './pages/InfoAccessPage';
import NotFoundPage from './pages/NotFoundPage';
import SearchResultsPage from './pages/SearchResultsPage';
import DynamicPage from './pages/DynamicPage';
import CMSDashboard from './components/cms/CMSDashboard';
import NewsArticlePage from './pages/NewsArticlePage';
import NewsPage from './pages/NewsPage';
import EventsPage from './pages/EventsPage';
import PDFDocumentPage from './pages/documents/PDFDocumentPage';
import PresentationViewerPage from './pages/projects/PresentationViewerPage';
import PresentationEmbedPage from './pages/projects/PresentationEmbedPage';
import DocumentsPage from './pages/DocumentsPage';
// import Home from './pages/Home';
// import CreatePost from './pages/CreatePost';


const App: React.FC = () => {
  return (
    <ErrorBoundary>
      <LanguageProvider>
        <NavigationProvider>
          <CMSProvider>
            <BrowserRouter>
          <Routes>
            <Route path="/" element={<Layout />}>
              <Route index element={<HomePage />} />
              
              {/* Училището */}
              <Route path="/school/history" element={<HistoryPage />} />
              <Route path="/school/patron" element={<PatronPage />} />
              <Route path="/school/team" element={<TeamPage />} />
              <Route path="/school/council" element={<CouncilPage />} />
              <Route path="/school/news" element={<SchoolNewsPage />} />

              {/* Документи */}
              <Route path="/documents" element={<DocumentsPage />} />
              <Route path="/documents/embed/:filename" element={<PDFDocumentPage />} />
              <Route path="/documents/pdf/:filename" element={<PDFDocumentPage />} />
              <Route path="/documents/*" element={<DynamicPage />} />

              {/* Работа по проекти */}
              <Route path="/projects/presentations/embed/:filename" element={<PresentationEmbedPage />} />
              <Route path="/projects/presentations/view/:filename" element={<PresentationViewerPage />} />
              <Route path="/projects/*" element={<DynamicPage />} />

              {/* Other main links */}
              <Route path="/useful-links" element={<UsefulLinksPage />} />
              <Route path="/gallery" element={<GalleryPage />} />
              <Route path="/contacts" element={<ContactsPage />} />
              <Route path="/info-access" element={<InfoAccessPage />} />
              <Route path="/search" element={<SearchResultsPage />} />
              
              {/* CMS Dashboard */}
              <Route path="/cms-dashboard" element={<CMSDashboard />} />
              
              {/* News */}
              <Route path="/news" element={<NewsPage />} />
              <Route path="/news/:id" element={<NewsArticlePage />} />
              
              {/* Events Page */}
              <Route path="/events" element={<EventsPage />} />
              
              {/* DB */}
              {/* <Route path="/create" element={<CreatePost />} /> */}

              {/* Dynamic pages - for CMS-created pages */}
              <Route path="/dynamic/:pageId" element={<DynamicPage />} />
              
              {/* 404 Page - catch-all for all unmatched routes */}
              <Route path="*" element={<NotFoundPage />} />
            </Route>
          </Routes>
            </BrowserRouter>
          </CMSProvider>
        </NavigationProvider>
      </LanguageProvider>
    </ErrorBoundary>
    
  );
};

export default App;
