package com.cia.mido;

import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;
import android.widget.EditText;

import org.json.JSONArray;
import org.json.JSONObject;

import java.util.ArrayList;

import it.gmariotti.cardslib.library.internal.Card;
import it.gmariotti.cardslib.library.internal.CardArrayAdapter;
import it.gmariotti.cardslib.library.internal.CardExpand;
import it.gmariotti.cardslib.library.internal.CardHeader;
import it.gmariotti.cardslib.library.internal.CardThumbnail;
import it.gmariotti.cardslib.library.view.CardListView;

public class MarketInsightActivity extends AppCompatActivity {
    private ArrayList<Card> cards = new ArrayList<>();

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        setContentView(R.layout.activity_market_insight);
    }

    @Override
    public void onBackPressed() {
        Intent menuIntent = new Intent(this, MenuActivity.class);
        startActivity(menuIntent);
        finish();
    }

    public void chooseSocialManager(View view) {
        Intent socialMediaIntent = new Intent(this, SocialMediaActivity.class);
        startActivity(socialMediaIntent);
        finish();
    }

    public void chooseNews(View view) {
        Intent newsIntent = new Intent(this, NewsActivity.class);
        startActivity(newsIntent);
        finish();
    }

    public void chooseMarketInsight(View view) {
        Intent marketIntent = new Intent(this, MarketInsightActivity.class);
        startActivity(marketIntent);
        finish();
    }

    public void showSearchResult(View view) {
        EditText keywordField = (EditText) findViewById(R.id.keyword);
        String keyword = keywordField.getText().toString();

        requestToServer(keyword, "Tokopedia");
        requestToServer(keyword, "Bukalapak");
        CardArrayAdapter mCardArrayAdapter = new CardArrayAdapter(this, cards);
        CardListView listView = (CardListView) this.findViewById(R.id.market_insight_cards);
        if (listView != null) {
            listView.setAdapter(mCardArrayAdapter);
        }
    }

    private void requestToServer(String keyword, String marketplaceName) {
        String url = getResources().getString(R.string.backEndUrl);
        String imgUrl = "";
        if (marketplaceName.equals("Tokopedia")) {
            url += getResources().getString(R.string.tokopediaGetInsightUrl) + "/" + keyword;
            imgUrl = "http://pro-rahasia.com/wp-content/uploads/2016/09/tokopedia.png";
        } else if (marketplaceName.equals("Bukalapak")) {
            url += getResources().getString(R.string.bukalapakGetInsightUrl) + "/" + keyword;
            imgUrl = "https://s3-ap-southeast-1.amazonaws.com/yesboss-newsletter/BUKALAPAK/logo+bukalapak-01.png";
        }
        Client client = new Client(url, null, "GET");
        client.execute();

        JSONObject response;
        try {
            do {
                Thread.sleep(1000);
                response = client.getResponse();
            } while (response == null);
            String productNum = response.getString("banyak_produk");
            String maxPrice = response.getString("harga_tertinggi");
            String minPrice = response.getString("harga_terendah");
            String avgPrice = response.getString("harga_rata");

            // Body
            String body = "Banyak produk: " + productNum + "\nHarga tertinggi: Rp"
                    + maxPrice + ",00\nHarga terendah: Rp" + minPrice
                    +",00\nHarga rata-rata: Rp" + avgPrice + ",00\n";

            // Card
            Card card = new Card(this);
            CardHeader headerCard = new CardHeader(this);
            headerCard.setTitle("Bukalapak");
            card.setTitle(body);
            card.addCardHeader(headerCard);

            // Thumbnail
            CardThumbnail thumbnailCard = new CardThumbnail(this);
            thumbnailCard.setUrlResource("https://s3-ap-southeast-1.amazonaws.com/yesboss-newsletter/BUKALAPAK/logo+bukalapak-01.png");
            card.addCardThumbnail(thumbnailCard);
            cards.add(card);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}
