import { QueryClient, QueryClientProvider } from 'react-query';
import { ReactQueryDevtools } from '@tanstack/react-query-devtools';

import VitePage from './ts/pages/VitePage';

const queryClient = new QueryClient();

const App = () => {
  return (
    <QueryClientProvider client={queryClient}>
      <VitePage />
      {/* <Database /> */}
      <ReactQueryDevtools />
    </QueryClientProvider>
  );
};

export default App;
