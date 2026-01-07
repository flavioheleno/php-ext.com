import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { Layout } from './components/Layout';
import { HomePage } from './pages/HomePage';
import { MatrixPage } from './pages/MatrixPage';
import { ExtensionDetailPage } from './pages/ExtensionDetailPage';

function App() {
  return (
    <BrowserRouter basename={import.meta.env.BASE_URL}>
      <Layout>
        <Routes>
          <Route path="/" element={<HomePage />} />
          <Route path="/matrix" element={<MatrixPage />} />
          <Route path="/ext/:name" element={<ExtensionDetailPage />} />
        </Routes>
      </Layout>
    </BrowserRouter>
  );
}

export default App;
