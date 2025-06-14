import { prefillFormFieldsFromURL, type PrefillFormFieldsOutput } from '@/ai/flows/prefill-form-fields-from-url';
import { CheckoutForm } from '@/components/checkout/checkout-form';
import { FooterSection } from '@/components/checkout/footer-section';
import { OrderSummarySection } from '@/components/checkout/order-summary-section';
import { ProductDetailsSection } from '@/components/checkout/product-details-section';

export default async function CheckoutPage({
  searchParams,
}: {
  searchParams?: { [key: string]: string | string[] | undefined };
}) {
  let initialData: PrefillFormFieldsOutput | undefined;
  let queryString = '';

  if (searchParams && Object.keys(searchParams).length > 0) {
    const cleanParams: Record<string, string | string[]> = {};
    for (const key of Object.keys(searchParams)) {
      const value = searchParams[key];
      if (value !== undefined) {
        // Ensure only string values are passed to URLSearchParams
        if (typeof value === 'string') {
          cleanParams[key] = value;
        } else if (Array.isArray(value) && value.every(item => typeof item === 'string')) {
          cleanParams[key] = value as string[];
        }
      }
    }
    if (Object.keys(cleanParams).length > 0) {
        queryString = new URLSearchParams(
            Object.entries(cleanParams).flatMap(([k, v]) => 
                Array.isArray(v) ? v.map(subV => [k, subV]) : [[k,v as string]]
            )
        ).toString();
    }
  }
  
  const currentUrl = `https://checkout.example.com/page${queryString ? `?${queryString}` : ''}`;

  try {
    if (queryString) {
      initialData = await prefillFormFieldsFromURL({ url: currentUrl });
    }
  } catch (error) {
    console.error("Error pre-filling form fields from URL:", error);
  }

  return (
    <div className="min-h-screen flex flex-col bg-background">
      
      <main className="flex-grow container mx-auto px-4 py-8">
        <div className="grid grid-cols-1 lg:grid-cols-3 lg:gap-8">
          {/* Left/Main Column */}
          <div className="lg:col-span-2 space-y-8">
            <ProductDetailsSection />
            <CheckoutForm initialData={initialData} />
          </div>

          {/* Right Column (Order Summary) - appears on top on mobile due to DOM order, then flex moves it */}
          <div className="lg:col-span-1 mt-8 lg:mt-0">
            <div className="lg:sticky lg:top-8">
              <OrderSummarySection />
            </div>
          </div>
        </div>
      </main>
      
      <FooterSection />
    </div>
  );
}
