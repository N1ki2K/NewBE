import React, { useState, useEffect } from 'react';
import { useLocation, Link } from 'react-router-dom';
import { apiService } from '../src/services/api';
import { News, Event, Staff } from '../types';
import { searchData, SearchResult } from '../src/search/searchData'; // Corrected import path
import { useTranslations } from '../context/LanguageContext';
import PageWrapper from '../components/PageWrapper';

const useQuery = () => {
  return new URLSearchParams(useLocation().search);
};

const SearchResultsPage: React.FC = () => {
  const query = useQuery().get('query') || '';
  const translations = useTranslations();
  const [results, setResults] = useState<SearchResult[]>([]);
  const [loading, setLoading] = useState<boolean>(true);

  useEffect(() => {
    const fetchAndSearchData = async () => {
      setLoading(true);
      try {
        // Fetch all data from the APIs
        const newsData = await apiService.get<News[]>('/news');
        const eventsData = await apiService.get<Event[]>('/events');
        const staffData = await apiService.get<Staff[]>('/staff');

        // Perform the search
        const searchResults = searchData(query, newsData, eventsData, staffData);
        setResults(searchResults);
      } catch (error) {
        console.error("Failed to fetch data for search:", error);
      } finally {
        setLoading(false);
      }
    };

    fetchAndSearchData();
  }, [query]);

  return (
    <PageWrapper title={`${translations.searchResultsFor} "${query}"`}>
      <div className="container mx-auto px-4 py-8">
        <h1 className="text-3xl font-bold mb-6">
          {translations.searchResultsFor} "<span className="text-blue-600">{query}</span>"
        </h1>
        {loading ? (
          <p>{translations.loading}...</p>
        ) : results.length > 0 ? (
          <div className="space-y-4">
            {results.map((result, index) => (
              <div key={index} className="p-4 border rounded-lg shadow-sm">
                <h2 className="text-xl font-semibold">
                  <Link to={result.link} className="text-blue-700 hover:underline">
                    {result.title}
                  </Link>
                  <span className="text-sm font-light text-gray-500 ml-2">({result.type})</span>
                </h2>
                <p className="text-gray-700 mt-2">
                  {result.content.substring(0, 150)}...
                </p>
              </div>
            ))}
          </div>
        ) : (
          <p>{translations.noResultsFound}</p>
        )}
      </div>
    </PageWrapper>
  );
};

export default SearchResultsPage;