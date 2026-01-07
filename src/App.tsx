import { HashRouter, Routes, Route } from 'react-router-dom';
import { DatasetProvider } from './context/DatasetContext';
import { Layout } from './components/Layout';
import { HomePage } from './pages/HomePage';
import { MatrixPage } from './pages/MatrixPage';
import { ExtensionDetailPage } from './pages/ExtensionDetailPage';

function App() {
  return (
    <DatasetProvider>
      <HashRouter>
        <Layout>
          <Routes>
            <Route path="/" element={<HomePage />} />
            <Route path="/matrix" element={<MatrixPage />} />
            <Route path="/ext/:name" element={<ExtensionDetailPage />} />
          </Routes>
        </Layout>
      </HashRouter>
    </DatasetProvider>
  );
}

export default App;
