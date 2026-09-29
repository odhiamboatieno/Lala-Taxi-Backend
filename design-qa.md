# Website redesign — visual QA

final result: passed

Scope: public marketing frontend, not production deployment or backend transaction certification.

## Source and capture

- Visual truth: user-attached `exec-0dfd1c97-6c53-45af-ab80-c1d605c97fb8.png` (Life moves / full-width city photograph). This overrides the earlier numbered homepage selection.
- Source copy: `design-review/website/references/exec-0dfd1c97-6c53-45af-ab80-c1d605c97fb8.png`.
- Safety composition source: `design-review/website/references/exec-ae225014-39a3-4856-88e3-57df6897ffa3.png`, with intentionally adapted safety content.
- Implementation: `http://127.0.0.1:8096/`, `/business`, `/safety`; Laravel-rendered read-only local preview.
- State: signed out, English, light theme, menu and download dialog closed.
- Source and implementation: 1024 × 1536 pixels; browser viewport 1024 × 1536 CSS pixels, deviceScaleFactor 1. No density scaling or browser chrome.
- Full-view combined evidence: `design-review/website/home-comparison.png`, `business-comparison.png`, `safety-comparison.png`. Source and rendered implementation are placed together, left and right respectively, in each 2048 × 1536 image.
- Focused evidence: `design-review/website/home-hero-comparison.png` (header excluded; full-width hero comparison). Checked headline, supporting text, logo on vehicle, buttons and subjects at readable scale.
- Responsive evidence: `<route>-<width>.png` in the same directory. Reviewed homepage at 390px and company sign-in at 390px, in addition to desktop comparisons.
- Generated screenshots and reference copies are local review artifacts, ignored by Git; scripts regenerate implementation evidence.

## Comparison history and resolved findings

1. [P1] Homepage was using the earlier split-layout concept. Replaced it with the explicitly attached full-bleed hero, two-service white band and pale-gold driver banner. Reused the pale city-background section below these sections as requested. Latest `home-comparison.png` shows the corrected composition.
2. [P2] Image height attributes inflated safety cards and created excessive vertical density. Added responsive intrinsic image height with explicit aspect ratios for editorial crops. Latest safety comparison shows three evenly sized photo cards.
3. [P2] Homepage service columns and padding pushed the driver banner too far below the selected reference. Reduced column padding and supporting copy; corrected CSS specificity so the compact section spacing wins over generic section rules. Latest homepage comparison places the driver band close to the reference boundary.
4. [P2] Safety display heading was too small for the centered reference. Increased its responsive display scale and reduced top spacing. Latest safety comparison preserves the oversized centered headline, supporting copy and wide photo hierarchy.
5. [P2] Hero copy sat too low, and the header logo was oversized. Adjusted hero padding balance and logo width. Focused hero comparison verifies the corrected text/CTA composition.
6. [P2] A missing icon and preview favicon request caused incomplete assets/browser errors. Replaced the unsupported icon with a local Lucide icon and corrected the read-only preview favicon base. Final browser run reports zero missing icons, broken images or console/page errors.

## Required fidelity surfaces

- Typography: locally hosted Inter variable, strong tightly spaced display headings, readable 16–20px supporting text, consistent weight hierarchy. Source is a raster concept, so exact original font metrics cannot be recovered. No clipped headings or unintended horizontal overflow in the checked widths.
- Layout: white header, full-bleed hero, white service band, diagonal driver-photo edge, warm city section. Desktop gutters and hero region are aligned with the reference. Mobile deliberately stacks copy before imagery to preserve readable text and visible people; the source supplies no mobile frame.
- Colors: shared gold, black, white, warm neutral and semantic tokens in `marketing-tokens.css`; no decorative CSS gradients. Solid photo overlay protects white text. It makes the generated photo slightly darker than the reference; this is an accepted legibility tradeoff.
- Images: generated photographic assets match Kenyan subjects, taxi branding, leafy city setting and natural daylight. Original supplied logo retained; local WebP assets are sharp and complete. Generated subjects/crops are variants, not pixel-identical extractions of the raster concept. No CSS art substitutes or placeholder images.
- Copy: all new public page copy, accessible labels and dialog text use `website.*` translations. Kenya-wide messaging replaces Nairobi-only service copy; no invented nationwide availability or company billing promises. Existing database-managed brand/legal/store information remains dynamic. Words within photographic signs/branding are raster artwork, not editable UI copy.
- Icons and controls: local Lucide outline icons, real anchors/buttons, visible keyboard focus, native dialog and details interactions, reduced-motion support. No fake form success states.

## Functional checks

- 60 browser checks: 12 routes at 1440, 1024, 768, 390 and 320 CSS-pixel widths.
- Verified HTTP success, no horizontal viewport overflow, no broken images, no untranslated keys or unresolved icons.
- Verified mobile navigation, Escape dismissal, download dialog opening/closing and focus restoration, safety accordions.
- Browser console and page errors: zero.
- PHPUnit: 5 tests, 46 assertions passed, with one existing PHPUnit configuration deprecation. Covers public view rendering, translation override/fallback, store-link fallback/filtering and preservation of the company sign-in form contract.
- JavaScript syntax and Git whitespace checks passed.

## Limitations and accepted differences

The local MySQL service is unavailable. The preview deliberately rejects POST requests and does not run database-backed middleware. Actual authentication, signup, email/SMS and payments are not end-to-end verified. Rider/driver registration forms, company onboarding and authenticated dashboards retain their existing implementations. No deployment performed.

Business and safety adapt the selected compositions to their own content, as requested. Generated photography and the supplied production logo differ subtly from the mock's raster artwork. These are accepted source-asset differences, not claims of pixel identity.

## Follow-up polish

[P3] If exact photographic identity is required, obtain the original clean background exports from the mock; current generated assets preserve the intended art direction. Additional locale translations should be written by native speakers; English fallback works now.

## Implementation checklist

- [x] Resolve attached homepage over earlier numbered option.
- [x] Implement Kenya-focused, translation-driven content and shared tokens.
- [x] Retain city background and responsive layouts.
- [x] Compare source and rendered screenshots together; fix P1/P2 findings and recapture.
- [x] Check browser interactions and view contracts.
- [ ] Verify backend workflows against a configured local/staging database before production release.
