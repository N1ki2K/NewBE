import React, { useState, useEffect } from 'react';
import { useLocation, Link } from 'react-router-dom';
import { useLanguage } from '../context/LanguageContext';
import PageWrapper from '../components/PageWrapper';
import { searchData } from '../src/search/searchData'; // Import the static data

const useQuery = () => {
  return new URLSearchParams(useLocation().search);
};

const SearchResultsPage: React.FC = () => {
  const query = useQuery().get('query') || '';
  const { language, t: translations } = useLanguage();
  const [results, setResults] = useState<any[]>([]);

  useEffect(() => {
    if (!query) {
      setResults([]);
      return;
    }

    const lowerCaseQuery = query.toLowerCase();

    const filteredResults = searchData.filter(page => {
      const title = page.title[language].toLowerCase();
      const content = page.content[language].toLowerCase();
      return title.includes(lowerCaseQuery) || content.includes(lowerCaseQuery);
    });

    setResults(filteredResults);

  }, [query, language]);

  return (
    <PageWrapper title={`${translations.searchResultsFor} "${query}"`}>
      <div className="container mx-auto px-4 py-8">
        <h1 className="text-3xl font-bold mb-6">
          {translations.searchResultsFor} "<span className="text-blue-600">{query}</span>"
        </h1>
        {results.length > 0 ? (
          <div className="space-y-4">
            {results.map((result, index) => (
              <div key={index} className="p-4 border rounded-lg shadow-sm">
                <h2 className="text-xl font-semibold">
                  <Link to={result.path} className="text-blue-700 hover:underline">
                    {result.title[language]}
                  </Link>
                </h2>
                <p className="text-gray-700 mt-2">
                  {result.content[language].substring(0, 250)}...
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